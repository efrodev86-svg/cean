<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\CicloEscolar;
use App\Models\Materia;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $ciclo = CicloEscolar::activo();

        return view('admin.dashboard', [
            'ciclo' => $ciclo,
            'totalAlumnos' => $ciclo
                ? Alumno::query()->where('ciclo_escolar_id', $ciclo->id)->count()
                : 0,
            'totalMaterias' => Materia::query()->count(),
            'totalCalificaciones' => Calificacion::query()->count(),
        ]);
    }
}
