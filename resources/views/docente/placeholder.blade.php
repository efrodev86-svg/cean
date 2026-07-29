<x-docente-layout :title="$title" :breadcrumb="$breadcrumb">
    <div class="docente-panel mx-auto max-w-2xl text-center">
        <p class="text-sm text-gray-400">{{ $message }}</p>
        <a href="{{ route('docente.dashboard') }}" class="mt-4 inline-block text-sm font-medium text-cean-cyan hover:underline">
            Volver al inicio
        </a>
    </div>
</x-docente-layout>
