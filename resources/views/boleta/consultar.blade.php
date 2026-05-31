<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-2xl font-bold text-gray-900">Consulta de boleta</h1>
        <p class="mt-2 text-sm text-gray-600">
            Ingresa tu matrícula y fecha de nacimiento para ver tus calificaciones.
        </p>
        @if ($cicloActivo)
            <p class="mt-1 text-xs text-indigo-600">Ciclo escolar: {{ $cicloActivo->nombre }}</p>
        @endif
    </div>

    @if (isset($error))
        <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
            {{ $error }}
        </div>
    @endif

    <form method="POST" action="{{ route('boleta.consultar') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="matricula" value="Matrícula" />
            <x-text-input id="matricula" name="matricula" type="text" class="mt-1 block w-full"
                :value="old('matricula', $matricula ?? '')" required autofocus />
            <x-input-error :messages="$errors->get('matricula')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento" />
            <x-text-input id="fecha_nacimiento" name="fecha_nacimiento" type="date" class="mt-1 block w-full"
                :value="old('fecha_nacimiento')" required />
            <x-input-error :messages="$errors->get('fecha_nacimiento')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">
            Consultar boleta
        </x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        <a href="{{ route('home') }}" class="text-indigo-600 hover:text-indigo-500">Volver al inicio</a>
    </p>
</x-guest-layout>
