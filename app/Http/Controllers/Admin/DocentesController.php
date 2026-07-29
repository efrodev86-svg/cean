<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocenteRequest;
use App\Http\Requests\UpdateDocenteRequest;
use App\Models\EstudioCursado;
use App\Models\GradoAcademico;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocentesController extends Controller
{
    public function index(Request $request): View
    {
        $scopeSedeId = $request->user()->sedeScopeId();
        $sedes = $this->sedesDisponibles($request);

        $docentes = User::query()
            ->docentes()
            ->with(['sedes:id,nombre', 'gradoAcademico:id,abreviatura'])
            // El encargado solo ve docentes asignados a su sede.
            ->when($scopeSedeId, fn ($q) => $q->whereHas('sedes', fn ($s) => $s->where('sedes.id', $scopeSedeId)))
            ->when($request->filled('sede') && ! $scopeSedeId, fn ($q) => $q->whereHas('sedes', fn ($s) => $s->where('sedes.id', (int) $request->input('sede'))))
            ->orderBy('primer_apellido')
            ->orderBy('segundo_apellido')
            ->orderBy('nombre')
            ->get();

        return view('admin.docentes.index', [
            'docentes' => $docentes,
            'sedes' => $sedes,
            'esAdminGlobal' => $request->user()->isAdmin(),
            'scopeSedeId' => $scopeSedeId,
            'filtros' => ['sede' => $request->input('sede')],
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.docentes.create', [
            'sedes' => $this->sedesDisponibles($request),
            'gradosAcademicos' => $this->gradosAcademicosActivos(),
            'esAdminGlobal' => $request->user()->isAdmin(),
        ]);
    }

    public function store(StoreDocenteRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $sedeIds = $this->sedesSeleccionadas($request);

        if (empty($sedeIds)) {
            return back()->withErrors(['sedes' => 'Selecciona al menos una sede.'])->withInput();
        }

        $existente = User::query()->where('email', $datos['email'])->first();

        // Reutilizar docente existente en vez de duplicarlo: solo se le añaden las sedes.
        if ($existente) {
            if (! $existente->isDocente()) {
                return back()->withErrors(['email' => 'Ese correo ya pertenece a otro usuario del sistema.'])->withInput();
            }

            $existente->sedes()->syncWithoutDetaching($sedeIds);

            return redirect()
                ->route('admin.docentes.index')
                ->with('success', "El docente {$existente->nombreConGrado()} ya existía; se agregó a las sedes seleccionadas.");
        }

        $docente = User::query()->create([
            ...$this->atributosDocente($datos),
            'role' => 'docente',
            'sede_id' => null,
            'password' => Hash::make($datos['password'] ?? Str::password(16)),
            'email_verified_at' => now(),
        ]);

        $docente->sedes()->sync($sedeIds);

        return redirect()
            ->route('admin.docentes.index')
            ->with('success', "Docente {$docente->nombreConGrado()} registrado correctamente.");
    }

    public function edit(Request $request, User $docente): View
    {
        abort_unless($docente->isDocente(), 404);
        $this->autorizarDocente($request, $docente);

        $docente->load([
            'sedes:id,nombre',
            'estudiosCursados' => fn ($q) => $q->orderByDesc('fecha'),
            'asignacionesDocente.materia:id,nombre,clave,semestre',
            'asignacionesDocente.grupo.cicloEscolar:id,nombre,activo',
            'asignacionesDocente.grupo.sede:id,nombre',
        ]);

        $estudiosCursados = $docente->estudiosCursados
            ->map(fn (EstudioCursado $estudio) => [
                'id' => $estudio->id,
                'descripcion' => $estudio->descripcion,
                'documento_probatorio' => $estudio->documento_probatorio,
                'fecha' => $estudio->fecha->format('Y-m-d'),
            ])
            ->values()
            ->all();

        $historialAsignaciones = $docente->asignacionesDocente
            ->sortByDesc(fn ($asignacion) => sprintf(
                '%d-%s-%02d-%s',
                $asignacion->grupo?->cicloEscolar?->activo ? 1 : 0,
                $asignacion->grupo?->cicloEscolar?->nombre ?? '',
                $asignacion->grupo?->semestre ?? 0,
                $asignacion->materia?->clave ?? '',
            ))
            ->groupBy(fn ($asignacion) => $asignacion->grupo?->cicloEscolar?->nombre ?? 'Sin ciclo')
            ->map(fn ($asignaciones, $ciclo) => [
                'ciclo' => $ciclo,
                'activo' => (bool) $asignaciones->first()?->grupo?->cicloEscolar?->activo,
                'asignaciones' => $asignaciones->values(),
            ])
            ->values();

        return view('admin.docentes.edit', [
            'docente' => $docente,
            'sedes' => $this->sedesDisponibles($request),
            'gradosAcademicos' => $this->gradosAcademicosActivos(),
            'esAdminGlobal' => $request->user()->isAdmin(),
            'estudiosCursados' => $estudiosCursados,
            'historialAsignaciones' => $historialAsignaciones,
        ]);
    }

    public function update(UpdateDocenteRequest $request, User $docente): RedirectResponse
    {
        abort_unless($docente->isDocente(), 404);
        $this->autorizarDocente($request, $docente);

        $datos = $request->validated();

        $docente->fill($this->atributosDocente($datos));

        if (! empty($datos['password'])) {
            $docente->password = Hash::make($datos['password']);
        }

        $docente->save();

        // Solo el administrador global reasigna sedes; el encargado no toca la membresía.
        if ($request->user()->isAdmin() && isset($datos['sedes'])) {
            $docente->sedes()->sync(
                collect($datos['sedes'])->map(fn ($v) => (int) $v)->unique()->all()
            );
        }

        return redirect()
            ->route('admin.docentes.index')
            ->with('success', "Docente {$docente->nombreConGrado()} actualizado.");
    }

    public function destroy(Request $request, User $docente): RedirectResponse
    {
        abort_unless($docente->isDocente(), 404);
        $this->autorizarDocente($request, $docente);

        // El administrador global elimina al docente; el encargado-docente conserva acceso de encargado.
        if ($request->user()->isAdmin()) {
            $docente->sedes()->detach();

            if ($docente->isEncargadoDocente()) {
                $docente->update(['role' => User::ROLE_ENCARGADO]);

                return redirect()
                    ->route('admin.docentes.index')
                    ->with('success', 'Se quitó el perfil docente; el usuario conserva acceso como encargado.');
            }

            $docente->delete();

            return redirect()
                ->route('admin.docentes.index')
                ->with('success', 'Docente eliminado.');
        }

        $docente->sedes()->detach($request->user()->sedeScopeId());

        return redirect()
            ->route('admin.docentes.index')
            ->with('success', 'Docente removido de tu sede.');
    }

    /**
     * @return array<int, int>
     */
    private function sedesSeleccionadas(Request $request): array
    {
        if ($request->user()->isAdmin()) {
            return collect($request->input('sedes', []))
                ->map(fn ($v) => (int) $v)
                ->unique()
                ->all();
        }

        return array_values(array_filter([$request->user()->sedeScopeId()]));
    }

    private function autorizarDocente(Request $request, User $docente): void
    {
        $scopeSedeId = $request->user()->sedeScopeId();

        if ($scopeSedeId === null) {
            return;
        }

        abort_unless(
            $docente->sedes()->where('sedes.id', $scopeSedeId)->exists(),
            403,
            'No puedes gestionar docentes de otra sede.'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Sede>
     */
    private function sedesDisponibles(Request $request)
    {
        $scopeSedeId = $request->user()->sedeScopeId();

        return Sede::query()
            ->when($scopeSedeId, fn ($q) => $q->where('id', $scopeSedeId))
            ->orderBy('nombre')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function atributosDocente(array $datos): array
    {
        return [
            'nombre' => $datos['nombre'],
            'primer_apellido' => $datos['primer_apellido'],
            'segundo_apellido' => $datos['segundo_apellido'] ?? null,
            'name' => User::nombreCompletoDesdePartes($datos),
            'email' => $datos['email'],
            'curp' => $datos['curp'],
            'grado_academico_id' => $datos['grado_academico_id'] ?? null,
            'tipo_contratacion' => $datos['tipo_contratacion'],
            'clave_plaza' => filled($datos['clave_plaza'] ?? null) ? $datos['clave_plaza'] : null,
            'nombre_plaza' => filled($datos['nombre_plaza'] ?? null) ? $datos['nombre_plaza'] : null,
            'celular' => $datos['celular'],
            'activo' => array_key_exists('activo', $datos)
                ? (bool) $datos['activo']
                : true,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, GradoAcademico>
     */
    private function gradosAcademicosActivos()
    {
        return GradoAcademico::query()
            ->where('activo', true)
            ->orderBy('abreviatura')
            ->get();
    }
}
