<x-admin-layout title="Nuevo docente" breadcrumb="Nuevo docente">
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <a href="{{ route('admin.docentes.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-400 transition hover:text-cean-cyan">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                Volver a docentes
            </a>
        </div>

        <div class="admin-panel">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-white">Registrar docente</h2>
                <p class="mt-1 text-sm text-gray-400">
                    Cada docente es un único registro. Si ya existe en otra sede con el mismo correo,
                    solo se le agregarán las sedes seleccionadas.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.docentes.store') }}" class="space-y-4">
                @csrf

                @include('admin.docentes._campos_docente')

                @if ($esAdminGlobal)
                    <div>
                        <label class="admin-form-label">Sedes donde imparte <span class="text-cean-red">*</span></label>
                        <div class="mt-1 grid gap-2 sm:grid-cols-2">
                            @foreach ($sedes as $sede)
                                <label class="flex items-center gap-2 rounded-lg border border-gray-700 px-3 py-2 text-sm text-gray-300">
                                    <input type="checkbox" name="sedes[]" value="{{ $sede->id }}"
                                        @checked(in_array((string) $sede->id, old('sedes', [])))
                                        class="rounded border-gray-600 bg-gray-800 text-cean-cyan">
                                    {{ $sede->nombre }}
                                </label>
                            @endforeach
                        </div>
                        @error('sedes')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
                    </div>
                @else
                    <p class="rounded-lg border border-gray-700 bg-gray-800/40 px-3 py-2 text-xs text-gray-400">
                        Se asignará a tu sede: <strong class="text-gray-200">{{ $sedes->first()?->nombre }}</strong>
                    </p>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="admin-form-label" for="password">Contraseña (portal)</label>
                        <input id="password" name="password" type="password" class="admin-form-input" autocomplete="new-password" placeholder="Opcional">
                        @error('password')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="admin-form-label" for="password_confirmation">Confirmar contraseña</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="admin-form-input" autocomplete="new-password">
                    </div>
                </div>
                <p class="text-[11px] text-gray-500">Si dejas la contraseña vacía se genera una automáticamente; el docente la puede restablecer luego.</p>

                <div class="flex justify-end gap-3 border-t border-gray-800 pt-4">
                    <a href="{{ route('admin.docentes.index') }}" class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-cean-primary">Crear docente</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
