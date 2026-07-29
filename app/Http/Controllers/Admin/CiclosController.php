<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCicloRequest;
use App\Http\Requests\UpdateCicloRequest;
use App\Http\Requests\UpdatePeriodoRequest;
use App\Models\CicloEscolar;
use App\Models\Periodo;
use App\Models\Sede;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CiclosController extends Controller
{
    public function index(Request $request): View
    {
        $scopeSedeId = $request->user()->sedeScopeId();

        $sedes = Sede::query()
            ->when($scopeSedeId, fn ($q) => $q->whereKey($scopeSedeId))
            ->orderBy('nombre')
            ->with(['ciclos' => function ($q) {
                $q->with('periodos')->withCount('alumnos')->orderByDesc('activo')->orderByDesc('nombre');
            }])
            ->get();

        // Los ciclos sin sede solo los gestiona un administrador global.
        $ciclosSinSede = $scopeSedeId
            ? collect()
            : CicloEscolar::query()
                ->whereNull('sede_id')
                ->with('periodos')
                ->withCount('alumnos')
                ->orderByDesc('nombre')
                ->get();

        return view('admin.ciclos.index', [
            'sedes' => $sedes,
            'ciclosSinSede' => $ciclosSinSede,
        ]);
    }

    public function store(StoreCicloRequest $request): RedirectResponse
    {
        $sedeId = (int) $request->validated('sede_id');

        // El encargado solo puede crear ciclos en su propia sede.
        $scopeSedeId = $request->user()->sedeScopeId();
        if ($scopeSedeId !== null) {
            $sedeId = $scopeSedeId;
        }

        $ciclo = DB::transaction(function () use ($request, $sedeId) {
            $ciclo = CicloEscolar::query()->create([
                'sede_id' => $sedeId,
                'nombre' => $request->validated('nombre'),
                'activo' => false,
            ]);

            $ciclo->crearPeriodosPredeterminados();

            return $ciclo;
        });

        return redirect()
            ->route('admin.ciclos.index')
            ->with('success', "Ciclo {$ciclo->nombre} creado con sus periodos A y B.");
    }

    public function update(UpdateCicloRequest $request, CicloEscolar $ciclo): RedirectResponse
    {
        $this->autorizarSede($request, $ciclo->sede_id);

        $datos = $request->validated();
        $activo = (bool) ($datos['activo'] ?? false);

        DB::transaction(function () use ($ciclo, $datos, $activo) {
            if ($activo) {
                // Solo puede haber un ciclo activo por sede.
                CicloEscolar::query()
                    ->where('sede_id', $ciclo->sede_id)
                    ->whereKeyNot($ciclo->id)
                    ->update(['activo' => false]);
            }

            $ciclo->update([
                'nombre' => $datos['nombre'],
                'activo' => $activo,
            ]);

            $ciclo->crearPeriodosPredeterminados();
        });

        return redirect()
            ->route('admin.ciclos.index')
            ->with('success', 'Ciclo escolar actualizado.');
    }

    public function updatePeriodo(UpdatePeriodoRequest $request, Periodo $periodo): RedirectResponse
    {
        $this->autorizarSede($request, $periodo->cicloEscolar?->sede_id);

        $datos = $request->validated();
        $datos['activo'] = (bool) ($datos['activo'] ?? false);

        DB::transaction(function () use ($periodo, $datos) {
            if ($datos['activo']) {
                Periodo::query()
                    ->where('ciclo_escolar_id', $periodo->ciclo_escolar_id)
                    ->whereKeyNot($periodo->id)
                    ->update(['activo' => false]);
            }

            $periodo->update($datos);
        });

        return redirect()
            ->route('admin.ciclos.index')
            ->with('success', "Periodo {$periodo->clave} actualizado.");
    }

    /**
     * Un encargado solo puede operar sobre recursos de su propia sede.
     */
    private function autorizarSede(Request $request, ?int $sedeId): void
    {
        $scopeSedeId = $request->user()->sedeScopeId();

        if ($scopeSedeId !== null && $scopeSedeId !== $sedeId) {
            abort(403, 'No puedes gestionar recursos de otra sede.');
        }
    }
}
