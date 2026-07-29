<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEstudioCursadoRequest;
use App\Http\Requests\UpdateEstudioCursadoRequest;
use App\Models\EstudioCursado;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EstudiosCursadosController extends Controller
{
    public function store(StoreEstudioCursadoRequest $request, User $docente): RedirectResponse
    {
        abort_unless($docente->isDocente(), 404);
        $this->autorizarDocente($request, $docente);

        $docente->estudiosCursados()->create($request->validated());

        return redirect()
            ->route('admin.docentes.edit', $docente)
            ->with('success', 'Estudio cursado registrado.')
            ->with('estudios_modal', true);
    }

    public function update(UpdateEstudioCursadoRequest $request, User $docente, EstudioCursado $estudioCursado): RedirectResponse
    {
        abort_unless($docente->isDocente(), 404);
        $this->autorizarEstudio($request, $docente, $estudioCursado);

        $estudioCursado->update($request->validated());

        return redirect()
            ->route('admin.docentes.edit', $docente)
            ->with('success', 'Estudio cursado actualizado.')
            ->with('estudios_modal', true);
    }

    public function destroy(Request $request, User $docente, EstudioCursado $estudioCursado): RedirectResponse
    {
        abort_unless($docente->isDocente(), 404);
        $this->autorizarEstudio($request, $docente, $estudioCursado);

        $estudioCursado->delete();

        return redirect()
            ->route('admin.docentes.edit', $docente)
            ->with('success', 'Estudio cursado eliminado.')
            ->with('estudios_modal', true);
    }

    private function autorizarEstudio(Request $request, User $docente, EstudioCursado $estudio): void
    {
        abort_unless($estudio->user_id === $docente->id, 404);

        $this->autorizarDocente($request, $docente);
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
}
