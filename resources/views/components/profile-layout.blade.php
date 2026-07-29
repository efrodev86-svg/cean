@props([
    'title' => 'Configurar cuenta',
    'breadcrumb' => 'Cuenta',
])

@if (auth()->user()->isAlumno())
    <x-alumno-layout :title="$title" :breadcrumb="$breadcrumb">
        {{ $slot }}
    </x-alumno-layout>
@elseif (auth()->user()->isDocente())
    <x-docente-layout :title="$title" :breadcrumb="$breadcrumb">
        {{ $slot }}
    </x-docente-layout>
@else
    <x-admin-layout :title="$title" :breadcrumb="$breadcrumb">
        {{ $slot }}
    </x-admin-layout>
@endif
