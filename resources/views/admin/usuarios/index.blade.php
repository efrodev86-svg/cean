@php
    $usuarioEditandoInicial = null;

    if (old('usuario_edit_id')) {
        $usuarioEditandoInicial = [
            'id' => (int) old('usuario_edit_id'),
            'name' => old('name'),
            'email' => old('email'),
            'role' => old('role'),
            'sede_id' => old('sede_id'),
            'codigo' => old('codigo'),
        ];
    }

    $abrirNuevoModal = (bool) old('nuevo_usuario') || (! $usuarioEditandoInicial && ($abrirNuevo ?? false));
@endphp

<x-admin-layout title="Usuarios" breadcrumb="Usuarios">
    <div
        class="mx-auto max-w-5xl space-y-6"
        x-data="{
            modalNuevo: @js($abrirNuevoModal),
            modalEditar: @js((bool) $usuarioEditandoInicial),
            usuarioEditando: @js($usuarioEditandoInicial),
            abrirEditar(usuario) { this.usuarioEditando = usuario; this.modalEditar = true; },
        }"
        @keydown.escape.window="modalNuevo = false; modalEditar = false"
    >
        @if (session('success'))
            <div class="admin-alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="admin-alert-error">{{ session('error') }}</div>
        @endif

        <div class="admin-panel">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-white">Personal del sistema</h2>
                    <p class="mt-1 text-sm text-gray-400">
                        @if ($vista === 'docentes')
                            Docentes registrados en el sistema. Desde aquí puedes cambiar su rol
                            (por ejemplo, a encargado-docente). El alta habitual de docentes está en
                            <a href="{{ route('admin.docentes.index') }}" class="text-cean-cyan hover:underline">Docentes</a>.
                        @else
                            Administradores y encargados de control escolar. Los docentes se gestionan
                            en la vista <strong class="text-gray-300">Docentes</strong> o al cambiar de pestaña abajo.
                        @endif
                    </p>
                </div>
                <button type="button" class="btn-cean-primary text-sm" @click="modalNuevo = true">
                    Nuevo usuario
                </button>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <a
                    href="{{ route('admin.usuarios.index', array_filter(['sede' => $filtros['sede'], 'rol' => $filtros['rol']])) }}"
                    class="rounded-lg px-3 py-1.5 text-xs font-semibold uppercase tracking-wide transition {{ $vista === 'control' ? 'bg-cean-cyan/20 text-cean-cyan' : 'border border-gray-600 text-gray-400 hover:border-gray-500 hover:text-gray-200' }}"
                >
                    Control escolar
                </a>
                <a
                    href="{{ route('admin.usuarios.index', array_filter(['vista' => 'docentes', 'sede' => $filtros['sede']])) }}"
                    class="rounded-lg px-3 py-1.5 text-xs font-semibold uppercase tracking-wide transition {{ $vista === 'docentes' ? 'bg-indigo-900/50 text-indigo-300' : 'border border-gray-600 text-gray-400 hover:border-gray-500 hover:text-gray-200' }}"
                >
                    Docentes
                </a>
            </div>

            <form method="GET" action="{{ route('admin.usuarios.index') }}" class="mt-4 grid gap-3 sm:grid-cols-3">
                @if ($vista === 'docentes')
                    <input type="hidden" name="vista" value="docentes">
                @endif
                <div>
                    <label class="admin-form-label" for="filtro-sede">Sede</label>
                    <select id="filtro-sede" name="sede" class="admin-form-input" onchange="this.form.submit()">
                        <option value="">Todas</option>
                        @foreach ($sedes as $sede)
                            <option value="{{ $sede->id }}" @selected($filtros['sede'] == $sede->id)>{{ $sede->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($vista === 'control')
                    <div>
                        <label class="admin-form-label" for="filtro-rol">Rol</label>
                        <select id="filtro-rol" name="rol" class="admin-form-input" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            <option value="admin" @selected($filtros['rol'] === 'admin')>Administrador</option>
                            <option value="encargado" @selected($filtros['rol'] === 'encargado')>Encargado</option>
                            <option value="encargado-docente" @selected($filtros['rol'] === 'encargado-docente')>Encargado-docente</option>
                        </select>
                    </div>
                @endif
            </form>
        </div>

        <div class="admin-panel">
            @if ($usuarios->isEmpty())
                <p class="text-sm text-gray-400">
                    @if ($vista === 'docentes')
                        No hay docentes para los filtros seleccionados.
                    @else
                        No hay usuarios de control escolar para los filtros seleccionados.
                    @endif
                </p>
            @else
                <div class="overflow-x-auto rounded-xl border border-gray-700">
                    <table class="min-w-full divide-y divide-gray-800 text-sm">
                        <thead class="bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Nombre</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Correo</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Rol</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Sede</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-400">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach ($usuarios as $usuario)
                                <tr class="hover:bg-gray-800/30">
                                    <td class="px-4 py-3 text-gray-200">{{ $usuario->name }}</td>
                                    <td class="px-4 py-3 text-gray-400">{{ $usuario->email }}</td>
                                    @php
                                        $rolEtiqueta = $usuario->roleLabel();
                                        $rolClase = match ($usuario->role) {
                                            'admin' => 'bg-cean-cyan/20 text-cean-cyan',
                                            'encargado' => 'bg-emerald-900/40 text-emerald-300',
                                            'encargado-docente' => 'bg-violet-900/40 text-violet-300',
                                            default => 'bg-indigo-900/40 text-indigo-300',
                                        };
                                    @endphp
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $rolClase }}">
                                            {{ $rolEtiqueta }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-400">
                                        @if ($usuario->sede)
                                            {{ $usuario->sede->nombre }}
                                        @elseif ($usuario->role === 'admin')
                                            Global
                                        @elseif ($vista === 'docentes' && $usuario->sedes->isNotEmpty())
                                            {{ $usuario->sedes->pluck('nombre')->join(', ') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button
                                            type="button"
                                            class="text-xs font-medium text-cean-cyan hover:underline"
                                            @click="abrirEditar({{ Js::from([
                                                'id' => $usuario->id,
                                                'name' => $usuario->name,
                                                'email' => $usuario->email,
                                                'role' => $usuario->role,
                                                'sede_id' => $usuario->sede_id,
                                                'codigo' => $usuario->codigo,
                                            ]) }})"
                                        >
                                            Editar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Modal: nuevo usuario --}}
        <div x-show="modalNuevo" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="modalNuevo = false"></div>
            <div class="admin-modal relative w-full max-w-lg" @click.stop>
                <div class="mb-5 flex items-start justify-between gap-4">
                    <h3 class="text-lg font-semibold text-white">Nuevo usuario</h3>
                    <button type="button" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-800 hover:text-white" @click="modalNuevo = false" aria-label="Cerrar">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('admin.usuarios.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="nuevo_usuario" value="1">
                    @php $repoblar = (bool) old('nuevo_usuario'); @endphp
                    <div>
                        <label class="admin-form-label" for="nuevo-name">Nombre completo <span class="text-cean-red">*</span></label>
                        <input id="nuevo-name" name="name" type="text" class="admin-form-input" value="{{ $repoblar ? old('name') : '' }}" required>
                        @if ($repoblar)@error('name')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                    </div>
                    <div>
                        <label class="admin-form-label" for="nuevo-email">Correo <span class="text-cean-red">*</span></label>
                        <input id="nuevo-email" name="email" type="email" class="admin-form-input" value="{{ $repoblar ? old('email') : '' }}" required>
                        @if ($repoblar)@error('email')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="admin-form-label" for="nuevo-role">Rol <span class="text-cean-red">*</span></label>
                            <select id="nuevo-role" name="role" class="admin-form-input" required>
                                <option value="admin" @selected(($repoblar ? old('role') : 'admin') === 'admin')>Administrador global</option>
                                <option value="encargado" @selected(($repoblar ? old('role') : null) === 'encargado')>Encargado de sede</option>
                                <option value="encargado-docente" @selected(($repoblar ? old('role') : null) === 'encargado-docente')>Encargado-docente</option>
                                <option value="docente" @selected(($repoblar ? old('role') : null) === 'docente')>Docente</option>
                            </select>
                            @if ($repoblar)@error('role')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                        </div>
                        <div>
                            <label class="admin-form-label" for="nuevo-sede">Sede</label>
                            <select id="nuevo-sede" name="sede_id" class="admin-form-input">
                                <option value="">Sin sede (admin global)</option>
                                @foreach ($sedes as $sede)
                                    <option value="{{ $sede->id }}" @selected(($repoblar ? old('sede_id') : ($sedePreseleccionada ?? null)) == $sede->id)>{{ $sede->nombre }}</option>
                                @endforeach
                            </select>
                            @if ($repoblar)@error('sede_id')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                        </div>
                    </div>
                    <div>
                        <label class="admin-form-label" for="nuevo-codigo">Código (docentes)</label>
                        <input id="nuevo-codigo" name="codigo" type="text" class="admin-form-input font-mono" value="{{ $repoblar ? old('codigo') : '' }}" placeholder="Opcional">
                        @if ($repoblar)@error('codigo')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="admin-form-label" for="nuevo-password">Contraseña inicial <span class="text-cean-red">*</span></label>
                            <input id="nuevo-password" name="password" type="password" class="admin-form-input" required autocomplete="new-password">
                            @if ($repoblar)@error('password')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                        </div>
                        <div>
                            <label class="admin-form-label" for="nuevo-password-confirm">Confirmar contraseña <span class="text-cean-red">*</span></label>
                            <input id="nuevo-password-confirm" name="password_confirmation" type="password" class="admin-form-input" required autocomplete="new-password">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800" @click="modalNuevo = false">Cancelar</button>
                        <button type="submit" class="btn-cean-primary">Crear usuario</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: editar usuario --}}
        <div x-show="modalEditar && usuarioEditando" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="modalEditar = false"></div>
            <div class="admin-modal relative w-full max-w-lg" @click.stop>
                <div class="mb-5 flex items-start justify-between gap-4">
                    <h3 class="text-lg font-semibold text-white">Editar usuario</h3>
                    <button type="button" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-800 hover:text-white" @click="modalEditar = false" aria-label="Cerrar">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form method="POST" x-bind:action="usuarioEditando ? '{{ url('/admin/usuarios') }}/' + usuarioEditando.id : '#'" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="usuario_edit_id" x-bind:value="usuarioEditando?.id">
                    <div>
                        <label class="admin-form-label">Nombre completo <span class="text-cean-red">*</span></label>
                        <input name="name" type="text" class="admin-form-input" x-model="usuarioEditando.name" required>
                        @if (old('usuario_edit_id'))@error('name')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                    </div>
                    <div>
                        <label class="admin-form-label">Correo <span class="text-cean-red">*</span></label>
                        <input name="email" type="email" class="admin-form-input" x-model="usuarioEditando.email" required>
                        @if (old('usuario_edit_id'))@error('email')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="admin-form-label">Rol <span class="text-cean-red">*</span></label>
                            <select name="role" class="admin-form-input" x-model="usuarioEditando.role" required>
                                <option value="admin">Administrador global</option>
                                <option value="encargado">Encargado de sede</option>
                                <option value="encargado-docente">Encargado-docente</option>
                                <option value="docente">Docente</option>
                            </select>
                        </div>
                        <div>
                            <label class="admin-form-label">Sede</label>
                            <select name="sede_id" class="admin-form-input" x-model="usuarioEditando.sede_id">
                                <option value="">Sin sede (admin global)</option>
                                @foreach ($sedes as $sede)
                                    <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                                @endforeach
                            </select>
                            @if (old('usuario_edit_id'))@error('sede_id')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                        </div>
                    </div>
                    <div>
                        <label class="admin-form-label">Código (docentes)</label>
                        <input name="codigo" type="text" class="admin-form-input font-mono" x-model="usuarioEditando.codigo" placeholder="Opcional">
                        @if (old('usuario_edit_id'))@error('codigo')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="admin-form-label">Nueva contraseña</label>
                            <input name="password" type="password" class="admin-form-input" autocomplete="new-password" placeholder="Dejar vacío para no cambiar">
                            @if (old('usuario_edit_id'))@error('password')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                        </div>
                        <div>
                            <label class="admin-form-label">Confirmar contraseña</label>
                            <input name="password_confirmation" type="password" class="admin-form-input" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="flex items-center justify-between gap-3 pt-2">
                        <button
                            type="button"
                            class="text-sm text-rose-400 hover:underline"
                            @click="$refs.formEliminar.submit()"
                        >
                            Eliminar usuario
                        </button>
                        <div class="flex gap-3">
                            <button type="button" class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800" @click="modalEditar = false">Cancelar</button>
                            <button type="submit" class="btn-cean-primary">Guardar cambios</button>
                        </div>
                    </div>
                </form>

                <form x-ref="formEliminar" method="POST" x-bind:action="usuarioEditando ? '{{ url('/admin/usuarios') }}/' + usuarioEditando.id : '#'" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
