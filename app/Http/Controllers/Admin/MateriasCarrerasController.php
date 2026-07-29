<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReorderMateriasRequest;
use App\Http\Requests\StoreLicenciaturaRequest;
use App\Http\Requests\StoreMateriaRequest;
use App\Http\Requests\UpdateLicenciaturaRequest;
use App\Http\Requests\UpdateMateriaRequest;
use App\Models\Licenciatura;
use App\Models\Materia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MateriasCarrerasController extends Controller
{
    public function index(Request $request): View
    {
        $licenciaturas = Licenciatura::query()
            ->withCount('materias')
            ->orderBy('nombre_corto')
            ->get();

        $licenciaturaSeleccionada = $request->filled('licenciatura')
            ? $licenciaturas->firstWhere('id', (int) $request->input('licenciatura'))
            : null;

        $materias = Materia::query()
            ->with('licenciatura')
            ->when($licenciaturaSeleccionada, fn ($q) => $q->where('licenciatura_id', $licenciaturaSeleccionada->id))
            ->when($request->filled('semestre'), fn ($q) => $q->where('semestre', (int) $request->input('semestre')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $termino = '%'.$request->string('q').'%';

                $q->where(function ($query) use ($termino) {
                    $query->where('nombre', 'like', $termino)
                        ->orWhere('clave', 'like', $termino);
                });
            })
            ->orderBy('semestre')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        return view('admin.materias.index', [
            'licenciaturas' => $licenciaturas,
            'licenciaturaSeleccionada' => $licenciaturaSeleccionada,
            'materias' => $materias,
            'filtros' => [
                'licenciatura' => $licenciaturaSeleccionada?->id,
                'semestre' => $request->input('semestre'),
                'q' => $request->input('q'),
            ],
        ]);
    }

    public function storeLicenciatura(StoreLicenciaturaRequest $request): RedirectResponse
    {
        $licenciatura = Licenciatura::query()->create([
            ...$request->validated(),
            'activa' => true,
        ]);

        return redirect()
            ->route('admin.materias', ['licenciatura' => $licenciatura->id])
            ->with('success', 'Licenciatura registrada correctamente.');
    }

    public function updateLicenciatura(UpdateLicenciaturaRequest $request, Licenciatura $licenciatura): RedirectResponse
    {
        $licenciatura->update($request->validated());

        return redirect()
            ->route('admin.materias', ['licenciatura' => $licenciatura->id])
            ->with('success', 'Licenciatura actualizada correctamente.');
    }

    public function storeMateria(StoreMateriaRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $datos['grado'] = $datos['semestre'].'° Semestre';

        if (empty($datos['orden'])) {
            $maxOrden = Materia::query()
                ->where('licenciatura_id', $datos['licenciatura_id'])
                ->where('semestre', $datos['semestre'])
                ->max('orden');

            $datos['orden'] = ($maxOrden ?? 0) + 1;
        }

        Materia::query()->create($datos);

        return redirect()
            ->route('admin.materias', ['licenciatura' => $datos['licenciatura_id']])
            ->with('success', 'Materia registrada correctamente.');
    }

    public function updateMateria(UpdateMateriaRequest $request, Materia $materia): RedirectResponse
    {
        $datos = $request->validated();
        $datos['grado'] = $datos['semestre'].'° Semestre';

        $materia->update($datos);

        return redirect()
            ->route('admin.materias', ['licenciatura' => $datos['licenciatura_id']])
            ->with('success', 'Materia actualizada correctamente.');
    }

    public function destroyMateria(Materia $materia): RedirectResponse
    {
        if ($materia->calificaciones()->exists()) {
            return back()->with('error', 'No se puede eliminar una materia con calificaciones registradas.');
        }

        $licenciaturaId = $materia->licenciatura_id;
        $materia->delete();

        return redirect()
            ->route('admin.materias', ['licenciatura' => $licenciaturaId])
            ->with('success', 'Materia eliminada correctamente.');
    }

    public function reorderMaterias(ReorderMateriasRequest $request): JsonResponse
    {
        $datos = $request->validated();
        $ids = $datos['materias'];

        if (count($ids) !== count(array_unique($ids))) {
            return response()->json(['message' => 'Hay materias duplicadas en el orden enviado.'], 422);
        }

        $totalEnSemestre = Materia::query()
            ->where('licenciatura_id', $datos['licenciatura_id'])
            ->where('semestre', $datos['semestre'])
            ->count();

        if ($totalEnSemestre !== count($ids)) {
            return response()->json(['message' => 'El listado de materias del semestre está incompleto.'], 422);
        }

        DB::transaction(function () use ($ids) {
            foreach ($ids as $indice => $id) {
                Materia::query()->whereKey($id)->update(['orden' => $indice + 1]);
            }
        });

        return response()->json(['message' => 'Orden actualizado correctamente.']);
    }

}
