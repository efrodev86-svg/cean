<x-landing-layout page-title="Consulta de boleta">
    <div class="w-full max-w-sm">
        {{-- Logo sobre la tarjeta --}}
        <header class="mb-6 text-center">
            <a
                href="{{ route('home') }}"
                class="mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-white p-2.5 shadow-lg transition hover:ring-2 hover:ring-cean-cyan/50 focus:outline-none focus:ring-2 focus:ring-cean-cyan"
            >
                <img
                    src="{{ asset('images/cean-mark.svg') }}"
                    alt="{{ config('cean.acronym') }}"
                    class="h-full w-full object-contain"
                >
            </a>
        </header>

        <div class="guest-card">
            {{-- Badges --}}
            <div class="mb-5 flex flex-wrap items-center justify-between gap-2">
                <span class="boleta-badge-public">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5a17.92 17.92 0 0 1-8.716-2.247m0 0A8.959 8.959 0 0 1 3 12a8.959 8.959 0 0 1 .284-2.253" />
                    </svg>
                    Consulta pública
                </span>

                @if ($cicloActivo)
                    <span class="boleta-badge-ciclo">Ciclo {{ $cicloActivo->nombre }}</span>
                @endif
            </div>

            <h1 class="login-title">Consulta de boleta</h1>
            <p class="login-subtitle">
                Ingresa tu matrícula y fecha de nacimiento registrados en control escolar.
            </p>

            <hr class="guest-card-divider">

            @if (isset($error))
                <div class="login-alert" role="alert">
                    {{ $error }}
                </div>
            @endif

            @if ($errors->any())
                <div class="login-alert" role="alert">
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('boleta.consultar') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="matricula" class="login-label">Matrícula</label>
                    <div class="login-input-wrap">
                        <svg class="login-input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm3.75-9.75h.008v.008H8.25v-.008Zm0 3h.008v.008H8.25v-.008Zm0 3h.008v.008H8.25v-.008Z" />
                        </svg>
                        <input
                            id="matricula"
                            name="matricula"
                            type="text"
                            class="login-input"
                            value="{{ old('matricula', $matricula ?? '') }}"
                            placeholder="Ej. 201559590000"
                            required
                            autofocus
                            autocomplete="off"
                        >
                    </div>
                    @error('matricula')
                        <p class="mt-1.5 font-mono text-xs text-cean-red">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="fecha_nacimiento" class="login-label">Fecha de nacimiento</label>
                    <div class="login-input-wrap">
                        <svg class="login-input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M4.5 8.25h15M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V8.25a2.25 2.25 0 0 0-2.25-2.25h-15a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        <input
                            id="fecha_nacimiento"
                            name="fecha_nacimiento"
                            type="date"
                            class="login-input pr-10 [color-scheme:dark]"
                            value="{{ old('fecha_nacimiento') }}"
                            required
                        >
                    </div>
                    @error('fecha_nacimiento')
                        <p class="mt-1.5 font-mono text-xs text-cean-red">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-cean-primary mt-2">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    Consultar boleta
                </button>
            </form>

            <nav class="mt-6 border-t border-gray-700/80 pt-5 text-center" aria-label="Enlaces relacionados">
                <a href="{{ route('login') }}" class="login-link">
                    ¿Eres personal? Iniciar sesión
                </a>
            </nav>
        </div>

        <a href="{{ route('home') }}" class="login-footer-link mx-auto mt-6 w-full max-w-sm">
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Volver al inicio
        </a>
    </div>
</x-landing-layout>
