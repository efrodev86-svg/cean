<x-admin-layout title="Editar alumno" breadcrumb="Editar alumno">
    <div class="mx-auto max-w-3xl space-y-6">
        @if (session('success'))
            <div class="admin-alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="admin-alert-error">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="admin-alert-error" role="alert">
                <p class="font-medium">No se pudieron guardar los cambios. Corrige lo siguiente:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <a href="{{ route('admin.alumnos', array_filter(['ciclo' => $alumno->ciclo_escolar_id, 'grupo' => $alumno->grupo_id])) }}" class="inline-flex items-center gap-1.5 text-sm text-gray-400 transition hover:text-cean-cyan">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                Volver a alumnos
            </a>
        </div>

        <div class="admin-panel">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-white">Editar alumno</h2>
                <p class="mt-1 text-sm text-gray-400">{{ $alumno->nombreFormal() }}@if ($alumno->matricula) · {{ $alumno->matricula }}@endif</p>
            </div>

            <form method="POST" action="{{ route('admin.alumnos.update', $alumno) }}" class="space-y-4">
                @csrf
                @method('PATCH')
                @include('admin.alumnos._campos_alumno', ['alumno' => $alumno])

                <div class="flex justify-end gap-3 border-t border-gray-800 pt-4">
                    <a href="{{ route('admin.alumnos') }}" class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800">
                        Cancelar
                    </a>
                    <button type="submit" class="btn-cean-primary">Guardar cambios</button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.alumnos.destroy', $alumno) }}" class="mt-4 border-t border-gray-800 pt-4" onsubmit="return confirm('¿Dar de baja a este alumno? Permanecerá en los registros, pero no podrá acceder al portal.');">
                @csrf
                @method('DELETE')
                @if ($alumno->estatus === \App\Support\AlumnoEstatus::BAJA_DEFINITIVA && ! ($alumno->user?->activo ?? false))
                    <p class="text-sm text-gray-500">Este alumno ya está dado de baja y sin acceso al sistema.</p>
                @else
                    <button type="submit" class="text-sm font-medium text-cean-red transition hover:underline">
                        Dar de baja alumno
                    </button>
                    <p class="mt-1.5 text-xs text-gray-500">No se borra el expediente: solo se marca baja definitiva y se desactiva su cuenta.</p>
                @endif
            </form>
        </div>
    </div>
</x-admin-layout>
