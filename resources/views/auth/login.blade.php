<x-landing-layout page-title="Iniciar sesión">
    <div class="w-full max-w-sm">
        {{-- Encabezado --}}
        <header class="mb-8 text-center">
            <a href="{{ route('home') }}" class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-xl bg-white p-2.5 shadow-lg transition hover:ring-2 hover:ring-cean-cyan/50 focus:outline-none focus:ring-2 focus:ring-cean-cyan">
                <img
                    src="{{ asset('images/cean-mark.svg') }}"
                    alt="{{ config('cean.acronym') }}"
                    class="h-full w-full object-contain"
                >
            </a>

            <h1 class="login-title">Iniciar sesión</h1>
            <p class="login-subtitle">
                {{ config('cean.full_name') }} — {{ config('cean.institution') }}
            </p>
            <p class="login-hint mt-1">
                Acceso para control escolar, docentes y alumnos
            </p>
        </header>

        @if (session('status'))
            <div class="login-alert-success" role="status">
                {{ session('status') }}
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

        <a href="{{ route('auth.google.redirect') }}" class="btn-login-google">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continuar con Google
        </a>
        <p class="login-hint mt-2 text-center">
            Usa tu cuenta institucional {{ '@'.\App\Support\GoogleOAuth::institutionalDomainHint() }}
        </p>

        <div class="login-divider" aria-hidden="true">
            <div class="login-divider-line">
                <div class="login-divider-border"></div>
            </div>
            <div class="login-divider-label">
                <span>O con correo institucional</span>
            </div>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ showPassword: false }">
            @csrf

            <div>
                <label for="email" class="login-label">Correo electrónico</label>
                <div class="login-input-wrap">
                    <svg class="login-input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        class="login-input"
                        value="{{ old('email') }}"
                        placeholder="control@escuela.test"
                        required
                        autofocus
                        autocomplete="username"
                    >
                </div>
            </div>

            <div>
                <label for="password" class="login-label">Contraseña</label>
                <div class="login-input-wrap">
                    <svg class="login-input-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                    <input
                        id="password"
                        name="password"
                        class="login-input"
                        :type="showPassword ? 'text' : 'password'"
                        required
                        autocomplete="current-password"
                    >
                    <button
                        type="button"
                        class="login-input-toggle"
                        @click="showPassword = !showPassword"
                        :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                    >
                        <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                        <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 pt-1">
                <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2">
                    <input
                        id="remember_me"
                        type="checkbox"
                        name="remember"
                        class="login-checkbox"
                    >
                    <span class="login-hint text-gray-400">Recordarme</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="login-link shrink-0">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>

            <button type="submit" class="btn-cean-primary mt-2">
                Iniciar sesión
            </button>
        </form>

        <nav class="mt-8 space-y-3 border-t border-gray-700/80 pt-6" aria-label="Enlaces relacionados">
            <a href="{{ route('boleta.index') }}" class="login-footer-link w-full">
                <svg class="h-4 w-4 shrink-0 text-cean-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                </svg>
                Consultar boleta (soy alumno)
            </a>
            <a href="{{ route('home') }}" class="login-footer-link w-full">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Volver al inicio
            </a>
        </nav>
    </div>
</x-landing-layout>
