<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGrupoRequest;
use App\Http\Requests\UpdateGrupoRequest;
use App\Models\Alumno;
use App\Models\CicloEscolar;
use App\Models\Grupo;
use App\Models\Licenciatura;
use App\Models\Sede;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GruposController extends Controller
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
            ->withCount('alumnos')
            ->when($cicloId, fn ($q) => $q->where('ciclo_escolar_id', $cicloId))
            ->when($request->filled('licenciatura'), fn ($q) => $q->where('licenciatura', $request->input('licenciatura')))
            ->orderBy('semestre')
            ->orderBy('licenciatura')
            ->orderBy('letra')
            ->get();

        $licenciaturas = Grupo::query()
            ->when($cicloId, fn ($q) => $q->where('ciclo_escolar_id', $cicloId))
            ->distinct()
            ->orderBy('licenciatura')
            ->pluck('licenciatura');

        $cicloSeleccionado = $ciclos->firstWhere('id', $cicloId);

        return view('admin.grupos.index', [
            'sedes' => $sedes,
            'ciclos' => $ciclos,
            'grupos' => $grupos,
            'licenciaturas' => $licenciaturas,
            'esAdminGlobal' => $esAdminGlobal,
            'sedeSeleccionada' => $sedes->firstWhere('id', $sedeId),
            'cicloSeleccionado' => $cicloSeleccionado,
            'totalAlumnos' => $grupos->sum('alumnos_count'),
            'filtros' => [
                'sede' => $sedeId,
                'ciclo' => $cicloId,
                'licenciatura' => $request->input('licenciatura'),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.grupos.create', $this->formularioContexto($request));
    }

    public function store(StoreGrupoRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $ciclo = CicloEscolar::query()->findOrFail($datos['ciclo_escolar_id']);
        $this->autorizarSede($request, $ciclo->sede_id);

        $grupo = Grupo::query()->create($this->atributosGrupo($datos, $ciclo));

        return redirect()
            ->route('admin.grupos', $this->filtrosDesdeCiclo($ciclo))
            ->with('success', "Grupo {$grupo->etiqueta()} creado correctamente.");
    }

    public function edit(Request $request, Grupo $grupo): View
    {
        $this->autorizarGrupo($request, $grupo);
        $grupo->load('cicloEscolar');
        $grupo->loadCount('alumnos');

        return view('admin.grupos.edit', [
            ...$this->formularioContexto($request, $grupo->ciclo_escolar_id),
            'grupo' => $grupo,
        ]);
    }

    public function update(UpdateGrupoRequest $request, Grupo $grupo): RedirectResponse
    {
        $this->autorizarGrupo($request, $grupo);

        $datos = $request->validated();
        $ciclo = CicloEscolar::query()->findOrFail($datos['ciclo_escolar_id']);
        $this->autorizarSede($request, $ciclo->sede_id);

        DB::transaction(function () use ($grupo, $datos, $ciclo) {
            $grupo->update($this->atributosGrupo($datos, $ciclo));
            $this->sincronizarAlumnosDelGrupo($grupo->fresh());
        });

        return redirect()
            ->route('admin.grupos', $this->filtrosDesdeCiclo($ciclo))
            ->with('success', "Grupo {$grupo->etiqueta()} actualizado.");
    }

    public function destroy(Request $request, Grupo $grupo): RedirectResponse
    {
        $this->autorizarGrupo($request, $grupo);
        $grupo->load('cicloEscolar');

        if ($grupo->alumnos()->exists()) {
            return redirect()
                ->route('admin.grupos', $this->filtrosDesdeCiclo($grupo->cicloEscolar))
                ->with('error', 'No puedes eliminar un grupo que tiene alumnos asignados.');
        }

        $filtros = $this->filtrosDesdeCiclo($grupo->cicloEscolar);
        $etiqueta = $grupo->etiqueta();
        $grupo->delete();

        return redirect()
            ->route('admin.grupos', $filtros)
            ->with('success', "Grupo {$etiqueta} eliminado.");
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

        $licenciaturasOpciones = Licenciatura::query()
            ->where('activa', true)
            ->orderBy('nombre_corto')
            ->get();

        if ($licenciaturasOpciones->isEmpty()) {
            $licenciaturasOpciones = collect([
                (object) ['nombre_corto' => 'ESPANOL', 'nombre' => 'Español'],
                (object) ['nombre_corto' => 'TELESECUNDARIA', 'nombre' => 'Telesecundaria'],
            ]);
        }

        return [
            'sedes' => $sedes,
            'ciclos' => $ciclos,
            'esAdminGlobal' => $esAdminGlobal,
            'cicloSeleccionado' => $ciclos->firstWhere('id', $cicloId),
            'licenciaturasOpciones' => $licenciaturasOpciones,
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

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function atributosGrupo(array $datos, CicloEscolar $ciclo): array
    {
        $semestre = (int) $datos['semestre'];
        $letra = strtoupper($datos['letra']);
        $licenciatura = $datos['licenciatura'];

        return [
            'sede_id' => $ciclo->sede_id,
            'ciclo_escolar_id' => $ciclo->id,
            'semestre' => $semestre,
            'letra' => $letra,
            'licenciatura' => $licenciatura,
            'nombre' => Grupo::construirNombre($semestre, $letra, $licenciatura),
        ];
    }

    private function sincronizarAlumnosDelGrupo(Grupo $grupo): void
    {
        Alumno::query()
            ->where('grupo_id', $grupo->id)
            ->update([
                'semestre' => $grupo->semestre,
                'licenciatura' => $grupo->licenciatura,
                'grupo' => $grupo->letra,
                'grado' => "{$grupo->semestre}° Semestre",
                'ciclo_escolar_id' => $grupo->ciclo_escolar_id,
            ]);
    }

    private function autorizarGrupo(Request $request, Grupo $grupo): void
    {
        $grupo->loadMissing('cicloEscolar');
        $this->autorizarSede($request, $grupo->sede_id ?? $grupo->cicloEscolar?->sede_id);
    }

    private function autorizarSede(Request $request, ?int $sedeId): void
    {
        $scopeSedeId = $request->user()->sedeScopeId();

        if ($scopeSedeId === null || $sedeId === null) {
            return;
        }

        abort_unless($scopeSedeId === $sedeId, 403, 'No puedes gestionar grupos de otra sede.');
    }

    /**
     * @return array<string, int>
     */
    private function filtrosDesdeCiclo(CicloEscolar $ciclo): array
    {
        return array_filter([
            'sede' => $ciclo->sede_id,
            'ciclo' => $ciclo->id,
        ]);
    }
}
