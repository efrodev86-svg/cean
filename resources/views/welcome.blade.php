<x-landing-layout>
    <div class="w-full max-w-3xl">
        {{-- Encabezado marca --}}
        <header class="mb-10 text-center">
            <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-xl bg-white p-2.5 shadow-lg">
                <img
                    src="{{ asset('images/cean-mark.svg') }}"
                    alt="{{ config('cean.acronym') }}"
                    class="h-full w-full object-contain"
                >
            </div>

            <h1 class="text-2xl font-bold uppercase tracking-wide text-cean-cyan sm:text-3xl">
                {{ config('cean.acronym') }}
            </h1>
            <p class="mx-auto mt-2 max-w-lg text-sm font-semibold uppercase tracking-wide text-gray-300 sm:text-base">
                {{ config('cean.full_name') }}
            </p>
            <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-gray-400">
                {{ config('cean.tagline') }}
            </p>
            <p class="mx-auto mt-1 text-xs text-gray-500">
                {{ config('cean.institution') }}
            </p>

            {{-- Acentos decorativos (cuatro puntos del logo) --}}
            <div class="mt-4 flex items-center justify-center gap-2" aria-hidden="true">
                <span class="h-2 w-2 rounded-full bg-cean-cyan"></span>
                <span class="h-2 w-2 rounded-full bg-cean-orange"></span>
                <span class="h-2 w-2 rounded-full bg-cean-red"></span>
                <span class="h-2 w-2 rounded-full bg-cean-navy ring-1 ring-gray-600"></span>
            </div>
        </header>

        {{-- Tarjetas de acceso --}}
        <div class="grid gap-4 md:grid-cols-2 md:gap-6">
            {{-- Alumno --}}
            <article class="landing-card">
                <div class="landing-icon">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                    </svg>
                </div>

                <h2 class="text-sm font-bold uppercase tracking-wide text-white">
                    Soy alumno
                </h2>
                <p class="mt-2 flex-1 text-sm text-gray-400">
                    Consultar mi boleta<br>
                    <span class="text-gray-500">Matrícula y fecha de nacimiento</span>
                </p>

                <a href="{{ route('boleta.index') }}" class="btn-cean-primary mt-6">
                    Consultar boleta
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </article>

            {{-- Personal escolar --}}
            <article class="landing-card">
                <div class="landing-icon">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                </div>

                <h2 class="text-sm font-bold uppercase tracking-wide text-white">
                    Personal escolar
                </h2>
                <p class="mt-2 flex-1 text-sm text-gray-400">
                    Docentes y control escolar<br>
                    <span class="text-gray-500">Correo institucional o Google</span>
                </p>

                @auth
                    @if (Auth::user()->isControlEscolar())
                        <a href="{{ route('admin.dashboard') }}" class="btn-cean-primary mt-6">
                            Panel de control escolar
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    @endif
                    @if (Auth::user()->isDocente() && ! Auth::user()->isAdmin())
                        <a href="{{ route('docente.dashboard') }}" class="{{ Auth::user()->isControlEscolar() ? 'btn-cean-outline mt-3' : 'btn-cean-primary mt-6' }}">
                            Portal docente
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn-cean-outline mt-6">
                        Iniciar sesión
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                        </svg>
                    </a>
                @endauth
            </article>
        </div>

        @auth
            <form method="POST" action="{{ route('logout') }}" class="mt-8 text-center">
                @csrf
                <button type="submit" class="text-sm text-gray-500 transition hover:text-cean-cyan focus:outline-none focus:ring-2 focus:ring-cean-cyan focus:ring-offset-2 focus:ring-offset-gray-800 rounded px-2 py-1">
                    Cerrar sesión
                </button>
            </form>
        @endauth
    </div>
</x-landing-layout>
