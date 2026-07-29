<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConsultarBoletaRequest;
use App\Models\Alumno;
use App\Models\CicloEscolar;
use App\Support\CalificacionEnLetra;
use App\Support\FechaBoleta;
use Carbon\Carbon;
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
        $hayCicloActivo = CicloEscolar::query()->where('activo', true)->exists();

        if (! $hayCicloActivo) {
            return view('boleta.consultar', [
                'cicloActivo' => null,
                'error' => 'No hay un ciclo escolar activo. Contacta a control escolar.',
            ]);
        }

        // El alumno determina su sede y ciclo (cada sede tiene su propio ciclo activo).
        $alumno = Alumno::query()
            ->where('matricula', $request->validated('matricula'))
            ->whereDate('fecha_nacimiento', $request->validated('fecha_nacimiento'))
            ->whereHas('cicloEscolar', fn ($q) => $q->where('activo', true))
            ->with('cicloEscolar.sede')
            ->first();

        if (! $alumno) {
            return view('boleta.consultar', [
                'cicloActivo' => CicloEscolar::activo(),
                'error' => 'No encontramos un alumno con esos datos. Verifica matrícula y fecha de nacimiento.',
                'matricula' => $request->validated('matricula'),
            ]);
        }

        $ciclo = $alumno->cicloEscolar;
        $sede = $ciclo->sede;
        $periodo = $ciclo->periodoParaSemestre($alumno->semestre);

        if ($periodo && ! $periodo->boletasDisponibles()) {
            return view('boleta.consultar', [
                'cicloActivo' => $ciclo,
                'error' => 'La boleta de este periodo aún no está disponible. Estará habilitada a partir del '
                    .$periodo->fecha_consulta_boletas->format('d/m/Y').'.',
                'matricula' => $request->validated('matricula'),
            ]);
        }

        $calificaciones = $alumno->calificaciones()
            ->where('semestre', $alumno->semestre)
            ->with('materia')
            ->get()
            ->sortBy(fn ($calificacion) => $calificacion->materia->nombre)
            ->values();

        $promedio = $calificaciones->isNotEmpty()
            ? round($calificaciones->avg('calificacion'), 1)
            : null;

        $fechaEmision = $periodo?->fecha_consulta_boletas
            ?? $ciclo->fecha_emision_boletas;

        $fechaEmision = $fechaEmision
            ? Carbon::parse($fechaEmision)
            : now();

        return view('boleta.mostrar', [
            'alumno' => $alumno,
            'ciclo' => $ciclo,
            'sede' => $sede,
            'calificaciones' => $calificaciones,
            'promedio' => $promedio,
            'promedioLetra' => $promedio !== null ? CalificacionEnLetra::decimal($promedio) : null,
            'fechaDestacada' => FechaBoleta::destacada($fechaEmision, $sede?->ciudad),
        ]);
    }
}
