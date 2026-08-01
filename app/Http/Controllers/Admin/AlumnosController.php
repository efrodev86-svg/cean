<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAlumnoRequest;
use App\Http\Requests\UpdateAlumnoRequest;
use App\Models\Alumno;
use App\Models\CicloEscolar;
use App\Models\Grupo;
use App\Models\Sede;
use App\Models\User;
use App\Support\AlumnoEstatus;
use App\Support\AlumnoFicha;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AlumnosController extends Controller
{
    public function index(Request $request): View
    {
        [
            'sedes' => $sedes,
            'sedeId' => $sedeId,
            'ciclos' => $ciclos,
            'cicloId' => $cicloId,
            'esAdminGlobal' => $esAdminGlobal,
        ] = $this->contextoFiltros($request);

        $grupos = Grupo::query()
            ->when($cicloId, fn ($q) => $q->where('ciclo_escolar_id', $cicloId))
            ->orderBy('semestre')
            ->orderBy('licenciatura')
            ->get();

        $alumnos = Alumno::query()
            ->with(['grupoEscolar', 'user:id,email,alumno_id,activo'])
            ->when($cicloId, fn ($q) => $q->where('ciclo_escolar_id', $cicloId))
            ->when($request->filled('grupo'), fn ($q) => $q->where('grupo_id', (int) $request->input('grupo')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $termino = '%'.$request->string('q')->trim().'%';
                $q->where(function ($q) use ($termino) {
                    $q->where('matricula', 'like', $termino)
                        ->orWhere('nombres', 'like', $termino)
                        ->orWhere('apellido_paterno', 'like', $termino)
                        ->orWhere('apellido_materno', 'like', $termino);
                });
            })
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombres')
            ->paginate(50)
            ->withQueryString();

        $cicloSeleccionado = $ciclos->firstWhere('id', $cicloId);

        return view('admin.alumnos.index', [
            'sedes' => $sedes,
            'ciclos' => $ciclos,
            'grupos' => $grupos,
            'alumnos' => $alumnos,
            'esAdminGlobal' => $esAdminGlobal,
            'sedeSeleccionada' => $sedes->firstWhere('id', $sedeId),
            'cicloSeleccionado' => $cicloSeleccionado,
            'filtros' => [
                'sede' => $sedeId,
                'ciclo' => $cicloId,
                'grupo' => $request->input('grupo'),
                'q' => $request->input('q'),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.alumnos.create', $this->formularioContexto($request));
    }

    public function store(StoreAlumnoRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $grupo = Grupo::query()->with('cicloEscolar')->findOrFail($datos['grupo_id']);
        $this->autorizarAlumnoPorSede($request, $grupo->sede_id);

        $alumno = DB::transaction(function () use ($datos, $grupo) {
            $alumno = Alumno::query()->create($this->atributosAlumno($datos, $grupo));
            $this->sincronizarUsuarioAlumno($alumno, $datos);

            return $alumno;
        });

        return redirect()
            ->route('admin.alumnos', $this->filtrosDesdeGrupo($grupo))
            ->with('success', "Alumno {$alumno->nombreFormal()} registrado correctamente.");
    }

    public function edit(Request $request, Alumno $alumno): View
    {
        $this->autorizarAlumno($request, $alumno);
        $alumno->load(['grupoEscolar.cicloEscolar', 'user']);

        return view('admin.alumnos.edit', [
            ...$this->formularioContexto($request, $alumno->grupoEscolar?->ciclo_escolar_id),
            'alumno' => $alumno,
        ]);
    }

    public function update(UpdateAlumnoRequest $request, Alumno $alumno): RedirectResponse
    {
        $this->autorizarAlumno($request, $alumno);

        $datos = $request->validated();
        $grupo = Grupo::query()->with('cicloEscolar')->findOrFail($datos['grupo_id']);
        $this->autorizarAlumnoPorSede($request, $grupo->sede_id);

        DB::transaction(function () use ($alumno, $datos, $grupo) {
            $alumno->update($this->atributosAlumno($datos, $grupo));
            $this->sincronizarUsuarioAlumno($alumno->fresh(), $datos);
        });

        $pestaña = $this->pestañaActiva($request);

        return redirect()
            ->route('admin.alumnos.edit', ['alumno' => $alumno, 'tab' => $pestaña])
            ->with('success', "Alumno {$alumno->nombreFormal()} actualizado.");
    }

    public function destroy(Request $request, Alumno $alumno): RedirectResponse
    {
        $this->autorizarAlumno($request, $alumno);
        $alumno->load(['grupoEscolar', 'user']);

        $filtros = $alumno->grupoEscolar
            ? $this->filtrosDesdeGrupo($alumno->grupoEscolar)
            : ['ciclo' => $alumno->ciclo_escolar_id];

        if ($alumno->estatus === AlumnoEstatus::BAJA_DEFINITIVA && ! ($alumno->user?->activo ?? false)) {
            return redirect()
                ->route('admin.alumnos.edit', $alumno)
                ->with('error', "El alumno {$alumno->nombreFormal()} ya está dado de baja y sin acceso al sistema.");
        }

        DB::transaction(function () use ($alumno) {
            $alumno->update(['estatus' => AlumnoEstatus::BAJA_DEFINITIVA]);

            if ($alumno->user && $alumno->user->isAlumno()) {
                $alumno->user->update(['activo' => false]);
            }
        });

        return redirect()
            ->route('admin.alumnos', $filtros)
            ->with('success', "Alumno {$alumno->nombreFormal()} dado de baja. Se conserva en registros sin acceso al portal.");
    }

    /**
     * @return array<string, mixed>
     */
    private function formularioContexto(Request $request, ?int $cicloPreferido = null): array
    {
        [
            'sedes' => $sedes,
            'sedeId' => $sedeId,
            'ciclos' => $ciclos,
            'cicloId' => $cicloId,
            'esAdminGlobal' => $esAdminGlobal,
        ] = $this->contextoFiltros($request, $cicloPreferido ?? $request->integer('ciclo') ?: null);

        $grupos = Grupo::query()
            ->with('cicloEscolar:id,nombre')
            ->when($sedeId, fn ($q) => $q->where('sede_id', $sedeId))
            ->when($cicloId, fn ($q) => $q->where('ciclo_escolar_id', $cicloId))
            ->orderByDesc('ciclo_escolar_id')
            ->orderBy('semestre')
            ->orderBy('licenciatura')
            ->get();

        return [
            'sedes' => $sedes,
            'ciclos' => $ciclos,
            'grupos' => $grupos,
            'esAdminGlobal' => $esAdminGlobal,
            'cicloSeleccionado' => $ciclos->firstWhere('id', $cicloId),
            'grupoSeleccionado' => $request->integer('grupo') ?: null,
        ];
    }

    /**
     * @return array{
     *     sedes: \Illuminate\Database\Eloquent\Collection<int, Sede>,
     *     sedeId: int|null,
     *     ciclos: \Illuminate\Database\Eloquent\Collection<int, CicloEscolar>,
     *     cicloId: int|null,
     *     esAdminGlobal: bool
     * }
     */
    private function contextoFiltros(Request $request, ?int $cicloPreferido = null): array
    {
        $scopeSedeId = $request->user()->sedeScopeId();
        $esAdminGlobal = $request->user()->isAdmin();

        $sedes = Sede::query()
            ->when($scopeSedeId, fn ($q) => $q->whereKey($scopeSedeId))
            ->orderBy('nombre')
            ->get();

        $sedeId = $scopeSedeId
            ?? ($request->filled('sede')
                ? (int) $request->input('sede')
                : (Sede::query()
                    ->when($scopeSedeId, fn ($q) => $q->whereKey($scopeSedeId))
                    ->whereHas('ciclos', fn ($q) => $q->where('activo', true))
                    ->orderBy('nombre')
                    ->value('id') ?? $sedes->first()?->id));

        $ciclos = CicloEscolar::query()
            ->when($sedeId, fn ($q) => $q->where('sede_id', $sedeId))
            ->orderByDesc('activo')
            ->orderByDesc('nombre')
            ->get();

        $cicloId = $cicloPreferido
            ?: ($request->filled('ciclo')
                ? (int) $request->input('ciclo')
                : ($ciclos->firstWhere('activo', true)?->id ?? $ciclos->first()?->id));

        return compact('sedes', 'sedeId', 'ciclos', 'cicloId', 'esAdminGlobal');
    }

    private function autorizarAlumno(Request $request, Alumno $alumno): void
    {
        $alumno->loadMissing('cicloEscolar');
        $this->autorizarAlumnoPorSede($request, $alumno->cicloEscolar?->sede_id);
    }

    private function autorizarAlumnoPorSede(Request $request, ?int $sedeId): void
    {
        $scopeSedeId = $request->user()->sedeScopeId();

        if ($scopeSedeId === null || $sedeId === null) {
            return;
        }

        abort_unless($scopeSedeId === $sedeId, 403, 'No puedes gestionar alumnos de otra sede.');
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function atributosAlumno(array $datos, Grupo $grupo): array
    {
        return [
            'matricula' => $datos['matricula'] ?? null,
            'nombres' => $datos['nombres'],
            'apellido_paterno' => $datos['apellido_paterno'],
            'apellido_materno' => $datos['apellido_materno'] ?? null,
            'curp' => isset($datos['curp']) ? strtoupper($datos['curp']) : null,
            'fecha_nacimiento' => $datos['fecha_nacimiento'],
            'grupo_id' => $grupo->id,
            'ciclo_escolar_id' => $grupo->ciclo_escolar_id,
            'semestre' => $grupo->semestre,
            'licenciatura' => $grupo->licenciatura,
            'grupo' => $grupo->letra,
            'grado' => "{$grupo->semestre}° Semestre",
            ...AlumnoFicha::atributosDesdeRegistro($datos),
        ];
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    private function sincronizarUsuarioAlumno(Alumno $alumno, array $datos): void
    {
        $alumno->loadMissing('grupoEscolar', 'cicloEscolar');

        $nombreCompleto = User::nombreCompletoDesdePartes([
            'nombre' => $datos['nombres'],
            'primer_apellido' => $datos['apellido_paterno'],
            'segundo_apellido' => $datos['apellido_materno'] ?? null,
        ]);

        $atributosUsuario = [
            'email' => $datos['email_acceso'],
            'name' => $nombreCompleto,
            'nombre' => $datos['nombres'],
            'primer_apellido' => $datos['apellido_paterno'],
            'segundo_apellido' => $datos['apellido_materno'] ?? null,
            'curp' => isset($datos['curp']) ? strtoupper($datos['curp']) : null,
            'celular' => $datos['celular'] ?? null,
            'role' => User::ROLE_ALUMNO,
            'activo' => array_key_exists('activo', $datos)
                ? (bool) $datos['activo'] && ! AlumnoEstatus::bloqueaAccesoPortal($datos['estatus'] ?? $alumno->estatus)
                : ! AlumnoEstatus::bloqueaAccesoPortal($datos['estatus'] ?? $alumno->estatus),
            'sede_id' => $alumno->grupoEscolar?->sede_id ?? $alumno->cicloEscolar?->sede_id,
            'email_verified_at' => now(),
        ];

        if (! empty($datos['password'])) {
            $atributosUsuario['password'] = Hash::make($datos['password']);
        } elseif (! User::query()->where('alumno_id', $alumno->id)->exists()) {
            $passwordInicial = filled($datos['matricula'] ?? null)
                ? (string) $datos['matricula']
                : str()->password(16);

            $atributosUsuario['password'] = Hash::make($passwordInicial);
        }

        User::query()->updateOrCreate(['alumno_id' => $alumno->id], $atributosUsuario);
    }

    /**
     * @return array<string, int>
     */
    private function filtrosDesdeGrupo(Grupo $grupo): array
    {
        return array_filter([
            'sede' => $grupo->sede_id,
            'ciclo' => $grupo->ciclo_escolar_id,
            'grupo' => $grupo->id,
        ]);
    }

    private function pestañaActiva(Request $request): string
    {
        $pestaña = (string) $request->input('tab', 'general');

        return in_array($pestaña, ['general', 'academicos', 'contacto', 'domicilio', 'salud'], true)
            ? $pestaña
            : 'general';
    }
}
