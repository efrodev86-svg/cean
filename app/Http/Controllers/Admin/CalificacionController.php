<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportarCalificacionesRequest;
use App\Models\Alumno;
use App\Models\CicloEscolar;
use App\Services\CalificacionImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CalificacionController extends Controller
{
    public function index(): View
    {
        $ciclo = CicloEscolar::activo();

        return view('admin.calificaciones.index', [
            'ciclo' => $ciclo,
            'alumnos' => $ciclo
                ? Alumno::query()
                    ->where('ciclo_escolar_id', $ciclo->id)
                    ->orderBy('grado')
                    ->orderBy('grupo')
                    ->orderBy('apellido_paterno')
                    ->withCount('calificaciones')
                    ->get()
                : collect(),
        ]);
    }

    public function importar(ImportarCalificacionesRequest $request, CalificacionImportService $importService): RedirectResponse
    {
        $ciclo = CicloEscolar::activo();

        if (! $ciclo) {
            return back()->with('error', 'Debes tener un ciclo escolar activo para importar calificaciones.');
        }

        $resultado = $importService->importFromCsv(
            $request->file('archivo'),
            (int) $request->validated('bimestre'),
            $ciclo
        );

        $mensaje = "Se importaron {$resultado['importadas']} calificación(es) correctamente.";

        if ($resultado['errores'] !== []) {
            session()->flash('import_errors', array_slice($resultado['errores'], 0, 10));

            if (count($resultado['errores']) > 10) {
                session()->flash('import_errors_more', count($resultado['errores']) - 10);
            }
        }

        return back()->with(
            $resultado['importadas'] > 0 ? 'success' : 'error',
            $resultado['importadas'] > 0 ? $mensaje : 'No se importó ninguna calificación. Revisa el archivo.'
        );
    }
}
