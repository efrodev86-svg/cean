<x-admin-layout title="Editar grupo" breadcrumb="Editar grupo">
    <div class="mx-auto max-w-3xl space-y-6">
        @if (session('success'))
            <div class="admin-alert-success">{{ session('success') }}</div>
        @endif

        <div>
            <a href="{{ route('admin.grupos', array_filter(['ciclo' => $grupo->ciclo_escolar_id])) }}" class="inline-flex items-center gap-1.5 text-sm text-gray-400 transition hover:text-cean-cyan">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                Volver a grupos
            </a>
        </div>

        <div class="admin-panel">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-white">Editar grupo</h2>
                <p class="mt-1 text-sm text-gray-400">{{ $grupo->etiqueta() }} · {{ $grupo->alumnos_count }} alumno(s)</p>
            </div>

            <form method="POST" action="{{ route('admin.grupos.update', $grupo) }}" class="space-y-4">
                @csrf
                @method('PATCH')
                @include('admin.grupos._campos_grupo', ['grupo' => $grupo])

                <div class="flex justify-end gap-3 border-t border-gray-800 pt-4">
                    <a href="{{ route('admin.grupos') }}" class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800">
                        Cancelar
                    </a>
                    <a href="{{ route('admin.grupos.asignaciones.edit', $grupo) }}" class="rounded-lg border border-cean-cyan/40 px-4 py-2 text-sm font-medium text-cean-cyan transition hover:bg-cean-cyan/10">
                        Asignar materias
                    </a>
                    <button type="submit" class="btn-cean-primary">Guardar cambios</button>
                </div>
            </form>

            @if ($grupo->alumnos_count === 0)
                <form method="POST" action="{{ route('admin.grupos.destroy', $grupo) }}" class="mt-4 border-t border-gray-800 pt-4" onsubmit="return confirm('¿Eliminar este grupo?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm font-medium text-cean-red transition hover:underline">
                        Eliminar grupo
                    </button>
                </form>
            @else
                <p class="mt-4 border-t border-gray-800 pt-4 text-xs text-gray-500">
                    No puedes eliminar un grupo con alumnos asignados. Reasígnalos antes de eliminar.
                </p>
            @endif
        </div>
    </div>
</x-admin-layout>
