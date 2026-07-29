<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGradoAcademicoRequest;
use App\Http\Requests\UpdateGradoAcademicoRequest;
use App\Models\GradoAcademico;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GradosAcademicosController extends Controller
{
    public function index(): View
    {
        $grados = GradoAcademico::query()
            ->withCount('docentes')
            ->orderBy('abreviatura')
            ->get();

        return view('admin.docentes.grados-academicos.index', [
            'grados' => $grados,
        ]);
    }

    public function store(StoreGradoAcademicoRequest $request): RedirectResponse
    {
        GradoAcademico::query()->create([
            ...$request->validated(),
            'activo' => true,
        ]);

        return redirect()
            ->route('admin.docentes.grados-academicos.index')
            ->with('success', 'Grado académico registrado correctamente.');
    }

    public function update(UpdateGradoAcademicoRequest $request, GradoAcademico $gradoAcademico): RedirectResponse
    {
        $datos = $request->validated();
        $datos['activo'] = (bool) ($datos['activo'] ?? false);

        $gradoAcademico->update($datos);

        return redirect()
            ->route('admin.docentes.grados-academicos.index')
            ->with('success', 'Grado académico actualizado.');
    }

    public function destroy(GradoAcademico $gradoAcademico): RedirectResponse
    {
        if ($gradoAcademico->docentes()->exists()) {
            return back()->with('error', 'No se puede eliminar: hay docentes con este grado asignado.');
        }

        $gradoAcademico->delete();

        return redirect()
            ->route('admin.docentes.grados-academicos.index')
            ->with('success', 'Grado académico eliminado.');
    }
}
