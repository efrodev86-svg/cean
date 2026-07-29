<x-admin-layout title="Nuevo grupo" breadcrumb="Nuevo grupo">
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <a href="{{ route('admin.grupos', array_filter(['ciclo' => $cicloSeleccionado?->id])) }}" class="inline-flex items-center gap-1.5 text-sm text-gray-400 transition hover:text-cean-cyan">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                Volver a grupos
            </a>
        </div>

        <div class="admin-panel">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-white">Registrar grupo</h2>
                <p class="mt-1 text-sm text-gray-400">
                    Define semestre, letra y licenciatura dentro de un ciclo escolar.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.grupos.store') }}" class="space-y-4">
                @csrf
                @include('admin.grupos._campos_grupo')

                <div class="flex justify-end gap-3 border-t border-gray-800 pt-4">
                    <a href="{{ route('admin.grupos') }}" class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-cean-primary">Crear grupo</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
