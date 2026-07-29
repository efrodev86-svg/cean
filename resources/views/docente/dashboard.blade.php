<x-docente-layout title="Portal docente" breadcrumb="Portal">
    <div class="docente-panel mx-auto max-w-4xl">
        <div class="mb-6 flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-gray-700 bg-gray-800 text-cean-cyan">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">Módulo docente</h2>
                <p class="mt-2 text-sm leading-relaxed text-gray-400">
                    Bienvenido, {{ auth()->user()->name }}.
                    Este espacio está reservado para widgets operativos, métricas de tus grupos asignados
                    y accesos directos a herramientas pedagógicas.
                </p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="docente-widget-placeholder h-24 rounded-xl border border-dashed border-gray-700 bg-gray-800/50"></div>
            <div class="docente-widget-placeholder h-24 rounded-xl border border-dashed border-gray-700 bg-gray-800/50"></div>
            <div class="docente-widget-placeholder h-24 rounded-xl border border-dashed border-gray-700 bg-gray-800/50"></div>
        </div>

        <p class="mt-6 text-center text-xs text-gray-500">
            Próximamente: captura de calificaciones, listado de alumnos por grupo y seguimiento de materias.
        </p>
    </div>
</x-docente-layout>
