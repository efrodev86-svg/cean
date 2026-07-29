<x-admin-layout title="Nuevo alumno" breadcrumb="Nuevo alumno">
    <div class="mx-auto max-w-3xl space-y-6">
        @if (session('error'))
            <div class="admin-alert-error">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="admin-alert-error" role="alert">
                <p class="font-medium">No se pudo registrar el alumno. Corrige lo siguiente:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <a href="{{ route('admin.alumnos', array_filter(['ciclo' => $cicloSeleccionado?->id, 'grupo' => $grupoSeleccionado])) }}" class="inline-flex items-center gap-1.5 text-sm text-gray-400 transition hover:text-cean-cyan">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                Volver a alumnos
            </a>
        </div>

        <div class="admin-panel">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-white">Registrar alumno</h2>
                <p class="mt-1 text-sm text-gray-400">
                    El alumno se inscribe en un grupo existente del ciclo
                    @if ($cicloSeleccionado)
                        <strong class="text-gray-300">{{ $cicloSeleccionado->nombre }}</strong>.
                    @else
                        escolar seleccionado.
                    @endif
                </p>
            </div>

            <form method="POST" action="{{ route('admin.alumnos.store') }}" class="space-y-4">
                @csrf
                @include('admin.alumnos._campos_alumno')

                <div class="flex justify-end gap-3 border-t border-gray-800 pt-4">
                    <a href="{{ route('admin.alumnos') }}" class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-cean-primary">Crear alumno</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
