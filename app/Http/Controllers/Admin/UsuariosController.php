<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UsuariosController extends Controller
{
    public function index(Request $request): View
    {
        $sedes = Sede::query()->orderBy('nombre')->get();
        $vista = $this->vistaActiva($request);

        $usuarios = User::query()
            ->with($vista === 'docentes' ? ['sedes:id,nombre'] : ['sede'])
            ->when($vista === 'docentes', fn ($q) => $q->where('role', User::ROLE_DOCENTE))
            ->when($vista === 'control', fn ($q) => $q->whereIn('role', User::rolesPersonalControl()))
            ->when($request->filled('sede') && $vista === 'control', fn ($q) => $q->where('sede_id', (int) $request->input('sede')))
            ->when($request->filled('sede') && $vista === 'docentes', fn ($q) => $q->whereHas(
                'sedes',
                fn ($s) => $s->where('sedes.id', (int) $request->input('sede'))
            ))
            ->when($request->filled('rol') && $vista === 'control', fn ($q) => $q->where('role', $request->input('rol')))
            ->orderBy('name')
            ->get();

        return view('admin.usuarios.index', [
            'usuarios' => $usuarios,
            'sedes' => $sedes,
            'vista' => $vista,
            'filtros' => [
                'sede' => $request->input('sede'),
                'rol' => $request->input('rol'),
            ],
            'sedePreseleccionada' => $request->input('sede'),
            'abrirNuevo' => $request->boolean('nuevo'),
        ]);
    }

    public function store(StoreUsuarioRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $datos['password'] = Hash::make($datos['password']);

        if (! in_array($datos['role'], [User::ROLE_DOCENTE, User::ROLE_ENCARGADO_DOCENTE], true)) {
            $datos['codigo'] = $datos['codigo'] ?? null;
        }

        if ($datos['role'] === User::ROLE_DOCENTE) {
            $datos['sede_id'] = null;
        }

        $usuario = User::query()->create($datos);

        $usuario->sincronizarSedeDocenteDesdeEncargado();

        return redirect()
            ->route('admin.usuarios.index', $this->parametrosVista($request, $usuario))
            ->with('success', "Usuario {$usuario->name} creado correctamente.");
    }

    public function update(UpdateUsuarioRequest $request, User $usuario): RedirectResponse
    {
        $datos = $request->validated();

        if (! empty($datos['password'])) {
            $datos['password'] = Hash::make($datos['password']);
        } else {
            unset($datos['password']);
        }

        if ($datos['role'] === User::ROLE_DOCENTE) {
            $datos['sede_id'] = null;
        }

        $usuario->update($datos);

        $usuario->sincronizarSedeDocenteDesdeEncargado();

        return redirect()
            ->route('admin.usuarios.index', $this->parametrosVista($request, $usuario))
            ->with('success', "Usuario {$usuario->name} actualizado.");
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        if ($request->user()->is($usuario)) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->delete();

        return redirect()
            ->route('admin.usuarios.index', $this->parametrosVista($request))
            ->with('success', 'Usuario eliminado.');
    }

    private function vistaActiva(Request $request): string
    {
        return $request->input('vista') === 'docentes' ? 'docentes' : 'control';
    }

    /**
     * @return array<string, mixed>
     */
    private function parametrosVista(Request $request, ?User $usuario = null): array
    {
        $vista = $this->vistaActiva($request);

        if ($usuario !== null) {
            $vista = $usuario->role === User::ROLE_DOCENTE ? 'docentes' : 'control';
        }

        return array_filter([
            'vista' => $vista,
            'sede' => $request->input('sede') ?? $usuario?->sede_id,
            'rol' => $request->input('rol'),
        ]);
    }
}
