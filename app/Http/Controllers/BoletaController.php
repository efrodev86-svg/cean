<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConsultarBoletaRequest;
use App\Models\Alumno;
use App\Models\CicloEscolar;
use Illuminate\View\View;

class BoletaController extends Controller
{
    public function index(): View
    {
        return view('boleta.consultar', [
            'cicloActivo' => CicloEscolar::activo(),
        ]);
    }

    public function consultar(ConsultarBoletaRequest $request): View
    {
        $ciclo = CicloEscolar::activo();

        if (! $ciclo) {
            return view('boleta.consultar', [
                'cicloActivo' => null,
                'error' => 'No hay un ciclo escolar activo. Contacta a control escolar.',
            ]);
        }

        $alumno = Alumno::query()
            ->where('matricula', $request->validated('matricula'))
            ->where('ciclo_escolar_id', $ciclo->id)
            ->whereDate('fecha_nacimiento', $request->validated('fecha_nacimiento'))
            ->with(['calificaciones.materia'])
            ->first();

        if (! $alumno) {
            return view('boleta.consultar', [
                'cicloActivo' => $ciclo,
                'error' => 'No encontramos un alumno con esos datos. Verifica matrícula y fecha de nacimiento.',
                'matricula' => $request->validated('matricula'),
            ]);
        }

        $calificacionesPorBimestre = $alumno->calificaciones
            ->sortBy(fn ($calificacion) => [$calificacion->bimestre, $calificacion->materia->nombre])
            ->groupBy('bimestre');

        return view('boleta.mostrar', [
            'alumno' => $alumno,
            'ciclo' => $ciclo,
            'calificacionesPorBimestre' => $calificacionesPorBimestre,
        ]);
    }
}
