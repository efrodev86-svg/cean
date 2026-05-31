<?php

use App\Http\Controllers\Admin\CalificacionController as AdminCalificacionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\BoletaController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/boleta', [BoletaController::class, 'index'])->name('boleta.index');
Route::post('/boleta', [BoletaController::class, 'consultar'])->name('boleta.consultar');

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/calificaciones', [AdminCalificacionController::class, 'index'])->name('calificaciones.index');
    Route::post('/calificaciones/importar', [AdminCalificacionController::class, 'importar'])->name('calificaciones.importar');
});

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
