@php
    $esAlumno = $user->isAlumno();
    $soloPassword = $user->soloPuedeCambiarPasswordEnPerfil();
    $alumno = $esAlumno ? $user->alumno : null;
@endphp

<x-profile-layout :title="$esAlumno ? 'Mi perfil' : 'Configurar cuenta'" :breadcrumb="$esAlumno ? 'Perfil' : 'Cuenta'">
    <div class="mx-auto max-w-3xl space-y-6">
        @if ($esAlumno && $alumno)
            <div class="admin-panel">
                <header>
                    <h2 class="text-lg font-semibold text-white">Datos académicos</h2>
                    <p class="mt-1 text-sm text-gray-400">
                        Información de tu inscripción. Si necesitas corregir matrícula o grupo, acude a control escolar.
                    </p>
                </header>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Matrícula</dt>
                        <dd class="mt-1 font-mono text-sm text-white">{{ $alumno->matricula }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Nombre completo</dt>
                        <dd class="mt-1 text-sm text-white">{{ $alumno->nombreCompleto() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Programa</dt>
                        <dd class="mt-1 text-sm text-white">{{ $alumno->licenciatura }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Grupo escolar</dt>
                        <dd class="mt-1 text-sm text-white">{{ $alumno->resumenAcademico() }}</dd>
                    </div>
                    @if ($alumno->estatus)
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Estatus</dt>
                            <dd class="mt-1 text-sm text-white">{{ $alumno->etiquetaEstatus() }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        @endif

        @if ($soloPassword)
            <div class="admin-panel">
                <header>
                    <h2 class="text-lg font-semibold text-white">Datos de acceso</h2>
                    <p class="mt-1 text-sm text-gray-400">
                        @if ($esAlumno)
                            Nombre y correo son gestionados por control escolar. Solo puedes cambiar tu contraseña.
                        @else
                            Nombre y correo son gestionados por el administrador. Solo puedes cambiar tu contraseña.
                        @endif
                    </p>
                </header>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Nombre</dt>
                        <dd class="mt-1 text-sm text-white">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">Correo electrónico</dt>
                        <dd class="mt-1 text-sm text-white">{{ $user->email }}</dd>
                    </div>
                </dl>
            </div>
        @else
            <div class="admin-panel">
                @include('profile.partials.update-profile-information-form')
            </div>
        @endif

        <div class="admin-panel">
            @include('profile.partials.update-password-form')
        </div>

        @unless ($soloPassword)
            <div class="admin-panel border-rose-900/40">
                @include('profile.partials.delete-user-form')
            </div>
        @endunless
    </div>
</x-profile-layout>
