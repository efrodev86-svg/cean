<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyncGrupoAsignacionesRequest;
use App\Models\Grupo;
use App\Models\GrupoMateriaDocente;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GrupoAsignacionesController extends Controller
{
    public function edit(Request $request, Grupo $grupo): View
    {
        $this->autorizarGrupo($request, $grupo);

        $grupo->load(['cicloEscolar', 'sede']);
        $grupo->loadCount('alumnos');

        $materias = $grupo->materiasDelPlan();
        $asignaciones = GrupoMateriaDocente::query()
            ->where('grupo_id', $grupo->id)
            ->get()
            ->keyBy('materia_id');

        $docentes = User::query()
            ->docentes()
            ->with('gradoAcademico:id,abreviatura')
            ->where('activo', true)
            ->whereHas('sedes', fn ($q) => $q->where('sedes.id', $grupo->sede_id))
            ->orderBy('primer_apellido')
            ->orderBy('segundo_apellido')
            ->orderBy('nombre')
            ->get();

        $docentesOpciones = $docentes->map(fn (User $docente) => [
            'id' => $docente->id,
            'nombre' => $docente->nombreConGrado(),
        ])->values()->all();

        $filas = $materias->values()->map(function ($materia, $indice) use ($asignaciones, $docentes) {
            $docenteId = old(
                "asignaciones.{$indice}.docente_id",
                $asignaciones->get($materia->id)?->docente_id
            );
            $docenteId = filled($docenteId) ? (int) $docenteId : null;
            $docente = $docenteId ? $docentes->firstWhere('id', $docenteId) : null;

            return [
                'materia_id' => $materia->id,
                'nombre' => $materia->nombre,
                'clave' => $materia->clave,
                'docente_id' => $docenteId,
                'etiqueta' => $docente?->nombreConGrado() ?? '',
                'abierto' => false,
                'destacado' => -1,
            ];
        })->all();

        return view('admin.grupos.asignaciones', [
            'grupo' => $grupo,
            'materias' => $materias,
            'docentesOpciones' => $docentesOpciones,
            'filas' => $filas,
            'totalMaterias' => $materias->count(),
        ]);
    }

    public function update(SyncGrupoAsignacionesRequest $request, Grupo $grupo): RedirectResponse
    {
        $this->autorizarGrupo($request, $grupo);

        $filas = collect($request->validated('asignaciones'))
            ->map(fn (array $fila) => [
                'materia_id' => (int) $fila['materia_id'],
                'docente_id' => filled($fila['docente_id'] ?? null) ? (int) $fila['docente_id'] : null,
            ])
            ->keyBy('materia_id');

        DB::transaction(function () use ($grupo, $filas) {
            $materiasPlan = $grupo->materiasDelPlan()->pluck('id');

            foreach ($materiasPlan as $materiaId) {
                $docenteId = $filas->get($materiaId)['docente_id'] ?? null;

                if ($docenteId === null) {
                    GrupoMateriaDocente::query()
                        ->where('grupo_id', $grupo->id)
                        ->where('materia_id', $materiaId)
                        ->delete();

                    continue;
                }

                GrupoMateriaDocente::query()->updateOrCreate(
                    [
                        'grupo_id' => $grupo->id,
                        'materia_id' => $materiaId,
                    ],
                    [
                        'docente_id' => $docenteId,
                    ],
                );
            }
        });

        return redirect()
            ->route('admin.grupos.asignaciones.edit', $grupo)
            ->with('success', "Asignaciones del grupo {$grupo->etiqueta()} guardadas.");
    }

    private function autorizarGrupo(Request $request, Grupo $grupo): void
    {
        $grupo->loadMissing('cicloEscolar');
        $sedeId = $grupo->sede_id ?? $grupo->cicloEscolar?->sede_id;
        $scopeSedeId = $request->user()->sedeScopeId();

        if ($scopeSedeId === null || $sedeId === null) {
            return;
        }

        abort_unless($scopeSedeId === $sedeId, 403, 'No puedes gestionar grupos de otra sede.');
    }
}
