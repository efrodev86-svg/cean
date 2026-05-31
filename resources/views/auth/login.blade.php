<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-xl font-bold text-gray-900">Control escolar</h1>
        <p class="mt-1 text-sm text-gray-600">Acceso para personal administrativo</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Contraseña" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Recordarme</span>
            </label>
        </div>

        <div class="mt-4 flex items-center justify-between">
            @if (Route::has('password.request'))
                <a class="rounded-md text-sm text-gray-600 underline hover:text-gray-900" href="{{ route('password.request') }}">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif

            <x-primary-button>Iniciar sesión</x-primary-button>
        </div>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        <a href="{{ route('home') }}" class="text-indigo-600 hover:text-indigo-500">Volver al inicio</a>
    </p>
</x-guest-layout>
