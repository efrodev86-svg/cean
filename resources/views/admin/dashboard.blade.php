<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Panel de control escolar
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            @if (! $ciclo)
                <div class="rounded-md bg-amber-50 p-4 text-sm text-amber-800">
                    No hay ciclo escolar activo. Configura uno en la base de datos antes de cargar calificaciones.
                </div>
            @else
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">
                    Ciclo activo: <strong>{{ $ciclo->nombre }}</strong>
                </div>
            @endif

            <div class="grid gap-6 sm:grid-cols-3">
                <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                    <p class="text-sm text-gray-500">Alumnos registrados</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalAlumnos }}</p>
                </div>
                <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                    <p class="text-sm text-gray-500">Materias</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalMaterias }}</p>
                </div>
                <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
                    <p class="text-sm text-gray-500">Calificaciones cargadas</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalCalificaciones }}</p>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="text-lg font-medium text-gray-900">Acciones rápidas</h3>
                <div class="mt-4">
                    <a href="{{ route('admin.calificaciones.index') }}"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                        Cargar calificaciones (CSV)
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
