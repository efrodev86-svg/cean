<x-alumno-layout title="Portal alumno" breadcrumb="Portal">
    <div class="docente-panel mx-auto max-w-4xl">
        <div class="mb-6 flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-gray-700 bg-gray-800 text-emerald-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">Portal alumno</h2>
                <p class="mt-2 text-sm leading-relaxed text-gray-400">
                    Bienvenido, {{ auth()->user()->name }}.
                    @if (auth()->user()->alumno)
                        Estás inscrito en {{ auth()->user()->alumno->resumenAcademico() }}.
                    @endif
                </p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <a href="{{ route('alumno.boleta') }}" class="rounded-xl border border-gray-800 bg-gray-800/50 p-5 transition hover:border-emerald-500/40 hover:bg-gray-800">
                <p class="text-sm font-semibold text-white">Mi boleta</p>
                <p class="mt-1 text-xs text-gray-500">Consulta tus calificaciones del periodo actual.</p>
            </a>
            <a href="{{ route('boleta.index') }}" class="rounded-xl border border-gray-800 bg-gray-800/50 p-5 transition hover:border-emerald-500/40 hover:bg-gray-800">
                <p class="text-sm font-semibold text-white">Consulta pública</p>
                <p class="mt-1 text-xs text-gray-500">Acceso con matrícula y fecha de nacimiento.</p>
            </a>
        </div>
    </div>
</x-alumno-layout>
