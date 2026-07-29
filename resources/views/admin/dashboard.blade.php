<x-admin-layout title="Panel de control escolar" breadcrumb="Inicio">
    <div class="admin-panel mx-auto max-w-4xl">
        <div class="flex flex-col items-center justify-center py-8 text-center sm:py-12">
            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-xl border border-gray-700 bg-gray-800 text-cean-cyan">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
            </div>
            <h2 class="text-lg font-bold text-white">Módulo administrativo</h2>
            <p class="mt-2 max-w-md text-sm text-gray-400">
                Contenido principal a integrar.
            </p>
            <p class="mt-6 text-xs text-gray-500">
                Bienvenido, {{ auth()->user()->name }}.
                Usa el menú lateral para gestionar alumnos, grupos, materias, docentes, calificaciones y ciclos escolares.
            </p>
            <a href="{{ route('admin.calificaciones.index') }}" class="btn-cean-primary mt-8 inline-flex">
                Cargar calificaciones (CSV)
            </a>
        </div>
    </div>
</x-admin-layout>
