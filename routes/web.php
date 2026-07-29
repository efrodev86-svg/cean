<?php

use App\Http\Controllers\Admin\AlumnosController;
use App\Http\Controllers\Admin\CalificacionController as AdminCalificacionController;
use App\Http\Controllers\Admin\CiclosController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DocentesController;
use App\Http\Controllers\Admin\EstudiosCursadosController;
use App\Http\Controllers\Admin\GradosAcademicosController;
use App\Http\Controllers\Admin\GrupoAsignacionesController;
use App\Http\Controllers\Admin\GruposController;
use App\Http\Controllers\Admin\MateriasCarrerasController;
use App\Http\Controllers\Admin\SedesController;
use App\Http\Controllers\Admin\UsuariosController;
use App\Http\Controllers\Alumno\DashboardController as AlumnoDashboardController;
use App\Http\Controllers\BoletaController;
use App\Http\Controllers\Docente\DashboardController as DocenteDashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/modulos/tablero', function () {
    return response()->file(base_path('docs/cean-modulos-tablero.html'));
});

Route::get('/modulos/tablero.csv', function () {
    return response()->file(base_path('docs/cean-modulos-notion.csv'), [
        'Content-Type' => 'text/csv; charset=UTF-8',
    ]);
});

Route::get('/boleta', [BoletaController::class, 'index'])->name('boleta.index');
Route::post('/boleta', [BoletaController::class, 'consultar'])->name('boleta.consultar');

Route::middleware(['auth', 'verified', 'control'])->prefix('admin')->name('admin.')->group(function () {
    // Pantallas compartidas por administrador global y encargado de sede.
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/alumnos', [AlumnosController::class, 'index'])->name('alumnos');
    Route::get('/alumnos/create', [AlumnosController::class, 'create'])->name('alumnos.create');
    Route::post('/alumnos', [AlumnosController::class, 'store'])->name('alumnos.store');
    Route::get('/alumnos/{alumno}/edit', [AlumnosController::class, 'edit'])->name('alumnos.edit');
    Route::patch('/alumnos/{alumno}', [AlumnosController::class, 'update'])->name('alumnos.update');
    Route::delete('/alumnos/{alumno}', [AlumnosController::class, 'destroy'])->name('alumnos.destroy');
    Route::get('/grupos', [GruposController::class, 'index'])->name('grupos');
    Route::get('/grupos/create', [GruposController::class, 'create'])->name('grupos.create');
    Route::post('/grupos', [GruposController::class, 'store'])->name('grupos.store');
    Route::get('/grupos/{grupo}/edit', [GruposController::class, 'edit'])->name('grupos.edit');
    Route::patch('/grupos/{grupo}', [GruposController::class, 'update'])->name('grupos.update');
    Route::delete('/grupos/{grupo}', [GruposController::class, 'destroy'])->name('grupos.destroy');
    Route::get('/grupos/{grupo}/asignaciones', [GrupoAsignacionesController::class, 'edit'])->name('grupos.asignaciones.edit');
    Route::put('/grupos/{grupo}/asignaciones', [GrupoAsignacionesController::class, 'update'])->name('grupos.asignaciones.update');
    Route::get('/docentes/grados-academicos', [GradosAcademicosController::class, 'index'])->name('docentes.grados-academicos.index');
    Route::post('/docentes/grados-academicos', [GradosAcademicosController::class, 'store'])->name('docentes.grados-academicos.store');
    Route::patch('/docentes/grados-academicos/{gradoAcademico}', [GradosAcademicosController::class, 'update'])->name('docentes.grados-academicos.update');
    Route::delete('/docentes/grados-academicos/{gradoAcademico}', [GradosAcademicosController::class, 'destroy'])->name('docentes.grados-academicos.destroy');
    Route::get('/docentes', [DocentesController::class, 'index'])->name('docentes.index');
    Route::get('/docentes/create', [DocentesController::class, 'create'])->name('docentes.create');
    Route::post('/docentes', [DocentesController::class, 'store'])->name('docentes.store');
    Route::post('/docentes/{docente}/estudios-cursados', [EstudiosCursadosController::class, 'store'])->name('docentes.estudios-cursados.store');
    Route::patch('/docentes/{docente}/estudios-cursados/{estudioCursado}', [EstudiosCursadosController::class, 'update'])->name('docentes.estudios-cursados.update');
    Route::delete('/docentes/{docente}/estudios-cursados/{estudioCursado}', [EstudiosCursadosController::class, 'destroy'])->name('docentes.estudios-cursados.destroy');
    Route::get('/docentes/{docente}/edit', [DocentesController::class, 'edit'])->name('docentes.edit');
    Route::patch('/docentes/{docente}', [DocentesController::class, 'update'])->name('docentes.update');
    Route::delete('/docentes/{docente}', [DocentesController::class, 'destroy'])->name('docentes.destroy');
    Route::get('/ciclos', [CiclosController::class, 'index'])->name('ciclos.index');
    Route::post('/ciclos', [CiclosController::class, 'store'])->name('ciclos.store');
    Route::patch('/ciclos/{ciclo}', [CiclosController::class, 'update'])->name('ciclos.update');
    Route::patch('/periodos/{periodo}', [CiclosController::class, 'updatePeriodo'])->name('periodos.update');
    Route::get('/calificaciones', [AdminCalificacionController::class, 'index'])->name('calificaciones.index');
    Route::post('/calificaciones/importar', [AdminCalificacionController::class, 'importar'])->name('calificaciones.importar');

    // Configuración institucional: solo administrador global.
    Route::middleware('admin')->group(function () {
        Route::get('/materias', [MateriasCarrerasController::class, 'index'])->name('materias');
        Route::post('/licenciaturas', [MateriasCarrerasController::class, 'storeLicenciatura'])->name('licenciaturas.store');
        Route::patch('/licenciaturas/{licenciatura}', [MateriasCarrerasController::class, 'updateLicenciatura'])->name('licenciaturas.update');
        Route::post('/materias', [MateriasCarrerasController::class, 'storeMateria'])->name('materias.store');
        Route::patch('/materias/reordenar', [MateriasCarrerasController::class, 'reorderMaterias'])->name('materias.reordenar');
        Route::patch('/materias/{materia}', [MateriasCarrerasController::class, 'updateMateria'])->name('materias.update');
        Route::delete('/materias/{materia}', [MateriasCarrerasController::class, 'destroyMateria'])->name('materias.destroy');
        Route::get('/sedes', [SedesController::class, 'index'])->name('sedes.index');
        Route::post('/sedes', [SedesController::class, 'store'])->name('sedes.store');
        Route::patch('/sedes/{sede}', [SedesController::class, 'update'])->name('sedes.update');
        Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios.index');
        Route::post('/usuarios', [UsuariosController::class, 'store'])->name('usuarios.store');
        Route::patch('/usuarios/{usuario}', [UsuariosController::class, 'update'])->name('usuarios.update');
        Route::delete('/usuarios/{usuario}', [UsuariosController::class, 'destroy'])->name('usuarios.destroy');
    });
});

Route::middleware(['auth', 'verified', 'docente'])->prefix('docente')->name('docente.')->group(function () {
    Route::get('/dashboard', [DocenteDashboardController::class, 'index'])->name('dashboard');
    Route::view('/materias', 'docente.placeholder', [
        'title' => 'Mis materias',
        'breadcrumb' => 'Mis materias',
        'message' => 'El listado de materias asignadas estará disponible próximamente.',
    ])->name('materias');
    Route::view('/calificaciones', 'docente.placeholder', [
        'title' => 'Captura calificaciones',
        'breadcrumb' => 'Calificaciones',
        'message' => 'La captura de calificaciones por grupo estará disponible próximamente.',
    ])->name('calificaciones');
    Route::view('/alumnos', 'docente.placeholder', [
        'title' => 'Mis alumnos',
        'breadcrumb' => 'Mis alumnos',
        'message' => 'El listado de alumnos por grupo estará disponible próximamente.',
    ])->name('alumnos');
});

Route::middleware(['auth', 'verified', 'alumno'])->prefix('alumno')->name('alumno.')->group(function () {
    Route::get('/dashboard', [AlumnoDashboardController::class, 'index'])->name('dashboard');
    Route::view('/boleta', 'alumno.placeholder', [
        'title' => 'Mi boleta',
        'breadcrumb' => 'Boleta',
        'message' => 'La consulta de boleta desde tu portal estará disponible próximamente.',
    ])->name('boleta');
    Route::view('/kardex', 'alumno.placeholder', [
        'title' => 'Historial académico',
        'breadcrumb' => 'Historial académico',
        'message' => 'El historial académico estará disponible próximamente.',
    ])->name('kardex');
});

Route::get('/dashboard', function () {
    return redirect(auth()->user()->homeRoute());
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
