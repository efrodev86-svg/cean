@php
    $sedeEditandoInicial = null;

    if (old('sede_edit_id')) {
        $sedeEditandoInicial = [
            'id' => (int) old('sede_edit_id'),
            'nombre' => old('nombre'),
            'clave' => old('clave'),
            'escuela' => old('escuela'),
            'director' => old('director'),
            'ciudad' => old('ciudad'),
            'logo' => old('logo'),
            'activa' => (bool) old('activa'),
        ];
    }
@endphp

<x-admin-layout title="Sedes" breadcrumb="Sedes">
    <div
        class="mx-auto max-w-5xl space-y-6"
        x-data="{
            modalNuevaSede: @js((bool) old('nueva_sede')),
            modalEditarSede: @js((bool) $sedeEditandoInicial),
            sedeEditando: @js($sedeEditandoInicial),
            abrirEditarSede(sede) { this.sedeEditando = sede; this.modalEditarSede = true; },
        }"
        @keydown.escape.window="modalNuevaSede = false; modalEditarSede = false"
    >
        @if (session('success'))
            <div class="admin-alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="admin-alert-error">{{ session('error') }}</div>
        @endif

        <div class="admin-panel">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-white">Sedes / planteles</h2>
                    <p class="mt-1 text-sm text-gray-400">
                        Cada sede maneja sus propios ciclos, periodos, grupos y alumnos. Los datos institucionales
                        (escuela, director, ciudad, logo) se imprimen en la boleta de sus alumnos.
                    </p>
                </div>
                <button type="button" class="btn-cean-primary text-sm" @click="modalNuevaSede = true">
                    Nueva sede
                </button>
            </div>
        </div>

        @forelse ($sedes as $sede)
            <section class="admin-panel">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="flex min-w-0 flex-1 gap-4">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-gray-700 bg-white p-1.5">
                            @if ($sede->logo && file_exists(public_path($sede->logo)))
                                <img src="{{ asset($sede->logo) }}" alt="Logo {{ $sede->nombre }}" class="max-h-full max-w-full object-contain">
                            @else
                                <span class="text-[10px] font-medium uppercase tracking-wide text-gray-400">Sin logo</span>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-3">
                            <h3 class="text-lg font-bold text-white">{{ $sede->nombre }}</h3>
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $sede->activa ? 'bg-green-900/40 text-green-300' : 'bg-gray-700 text-gray-400' }}">
                                {{ $sede->activa ? 'Activa' : 'Inactiva' }}
                            </span>
                        </div>
                        <p class="mt-1 font-mono text-xs text-cean-cyan">{{ $sede->clave }}</p>
                        <dl class="mt-3 grid gap-x-6 gap-y-1 text-xs sm:grid-cols-2">
                            <div class="flex gap-2"><dt class="text-gray-500">Escuela:</dt><dd class="text-gray-300">{{ $sede->escuela ?: '— (config)' }}</dd></div>
                            <div class="flex gap-2"><dt class="text-gray-500">Director:</dt><dd class="text-gray-300">{{ $sede->director ?: '— (config)' }}</dd></div>
                            <div class="flex gap-2"><dt class="text-gray-500">Ciudad:</dt><dd class="text-gray-300">{{ $sede->ciudad ?: '— (config)' }}</dd></div>
                            <div class="flex gap-2"><dt class="text-gray-500">Ciclos:</dt><dd class="text-gray-300">{{ $sede->ciclos_count }}</dd></div>
                        </dl>

                        <div class="mt-4 border-t border-gray-700/60 pt-3">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-xs font-medium text-gray-300">Encargados de control escolar</p>
                                <a href="{{ route('admin.usuarios.index', ['sede' => $sede->id, 'nuevo' => 1]) }}" class="text-xs font-medium text-cean-cyan hover:underline">
                                    + Agregar encargado
                                </a>
                            </div>
                            @if ($sede->encargados->isEmpty())
                                <p class="mt-1 text-xs text-gray-500">Sin encargados asignados.</p>
                            @else
                                <ul class="mt-1 space-y-0.5">
                                    @foreach ($sede->encargados as $encargado)
                                        <li class="text-xs text-gray-400">{{ $encargado->name }} · <span class="text-gray-500">{{ $encargado->email }}</span></li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg border border-gray-600 px-3 py-1.5 text-xs font-medium text-gray-200 transition hover:border-cean-cyan/50 hover:text-cean-cyan"
                        @click="abrirEditarSede({{ Js::from([
                            'id' => $sede->id,
                            'nombre' => $sede->nombre,
                            'clave' => $sede->clave,
                            'escuela' => $sede->escuela,
                            'director' => $sede->director,
                            'ciudad' => $sede->ciudad,
                            'logo_url' => ($sede->logo && file_exists(public_path($sede->logo))) ? asset($sede->logo) : null,
                            'activa' => $sede->activa,
                        ]) }})"
                    >
                        Editar sede
                    </button>
                </div>
            </section>
        @empty
            <div class="admin-panel text-center">
                <p class="text-sm text-gray-400">No hay sedes registradas. Crea la primera con <strong class="text-gray-300">Nueva sede</strong>.</p>
            </div>
        @endforelse

        {{-- Modal: nueva sede --}}
        <div x-show="modalNuevaSede" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="modalNuevaSede = false"></div>
            <div class="admin-modal relative w-full max-w-lg" @click.stop>
                <div class="mb-5 flex items-start justify-between gap-4">
                    <h3 class="text-lg font-semibold text-white">Nueva sede</h3>
                    <button type="button" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-800 hover:text-white" @click="modalNuevaSede = false" aria-label="Cerrar">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('admin.sedes.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="nueva_sede" value="1">
                    @include('admin.sedes._campos', ['old_prefix' => 'nueva_sede'])
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800" @click="modalNuevaSede = false">Cancelar</button>
                        <button type="submit" class="btn-cean-primary">Registrar sede</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: editar sede --}}
        <div x-show="modalEditarSede && sedeEditando" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="modalEditarSede = false"></div>
            <div class="admin-modal relative w-full max-w-lg" @click.stop>
                <div class="mb-5 flex items-start justify-between gap-4">
                    <h3 class="text-lg font-semibold text-white">Editar sede</h3>
                    <button type="button" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-800 hover:text-white" @click="modalEditarSede = false" aria-label="Cerrar">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form method="POST" x-bind:action="sedeEditando ? '{{ url('/admin/sedes') }}/' + sedeEditando.id : '#'" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="sede_edit_id" x-bind:value="sedeEditando?.id">
                    <div>
                        <label class="admin-form-label">Nombre <span class="text-cean-red">*</span></label>
                        <input name="nombre" type="text" class="admin-form-input" x-model="sedeEditando.nombre" required>
                        @if (old('sede_edit_id'))@error('nombre')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                    </div>
                    <div>
                        <label class="admin-form-label">Clave (CCT) <span class="text-cean-red">*</span></label>
                        <input name="clave" type="text" class="admin-form-input font-mono" x-model="sedeEditando.clave" required>
                        @if (old('sede_edit_id'))@error('clave')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                    </div>
                    <div>
                        <label class="admin-form-label">Escuela (boleta)</label>
                        <input name="escuela" type="text" class="admin-form-input" x-model="sedeEditando.escuela" placeholder="Vacío = usar valor por defecto">
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="admin-form-label">Director</label>
                            <input name="director" type="text" class="admin-form-input" x-model="sedeEditando.director" placeholder="Vacío = config">
                        </div>
                        <div>
                            <label class="admin-form-label">Ciudad</label>
                            <input name="ciudad" type="text" class="admin-form-input" x-model="sedeEditando.ciudad" placeholder="Vacío = config">
                        </div>
                    </div>
                    <div>
                        <label class="admin-form-label">Logo (boleta)</label>
                        <div x-show="sedeEditando?.logo_url" class="mb-2 flex h-14 w-14 items-center justify-center overflow-hidden rounded-lg border border-gray-700 bg-white p-1">
                            <img x-bind:src="sedeEditando?.logo_url" alt="" class="max-h-full max-w-full object-contain">
                        </div>
                        <input
                            name="logo"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            class="admin-form-input file:mr-3 file:rounded-md file:border-0 file:bg-cean-cyan/20 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-cean-cyan hover:file:bg-cean-cyan/30"
                        >
                        <p class="mt-1 text-[11px] text-gray-500">Opcional. JPG, PNG o WebP · máximo 2 MB.</p>
                        @if (old('sede_edit_id'))@error('logo')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-300">
                        <input type="hidden" name="activa" value="0">
                        <input type="checkbox" name="activa" value="1" x-model="sedeEditando.activa" class="rounded border-gray-600 bg-gray-800 text-cean-cyan">
                        Sede activa
                    </label>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800" @click="modalEditarSede = false">Cancelar</button>
                        <button type="submit" class="btn-cean-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
