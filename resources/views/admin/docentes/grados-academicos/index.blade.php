@php
    $gradoEditandoInicial = null;

    if (old('grado_edit_id')) {
        $gradoEditandoInicial = [
            'id' => (int) old('grado_edit_id'),
            'abreviatura' => old('abreviatura'),
            'activo' => (bool) old('activo'),
            'docentes_count' => (int) old('docentes_count', 0),
        ];
    }
@endphp

<x-admin-layout title="Grados académicos" breadcrumb="Grados académicos">
    <div
        class="mx-auto max-w-3xl space-y-6"
        x-data="{
            modalEditarGrado: @js((bool) $gradoEditandoInicial),
            gradoEditando: @js($gradoEditandoInicial),
            abrirEditarGrado(grado) { this.gradoEditando = grado; this.modalEditarGrado = true; },
        }"
        @keydown.escape.window="modalEditarGrado = false"
    >
        @if (session('success'))
            <div class="admin-alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="admin-alert-error">{{ session('error') }}</div>
        @endif

        <div>
            <a href="{{ route('admin.docentes.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-400 transition hover:text-cean-cyan">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                Volver a docentes
            </a>
        </div>

        <div class="admin-panel space-y-6">
            <div>
                <h2 class="text-lg font-semibold text-white">Grados académicos</h2>
                <p class="mt-1 text-sm text-gray-400">
                    Catálogo de abreviaturas para docentes (ej. Dr., Mtro., Lic.).
                </p>
            </div>

            <form method="POST" action="{{ route('admin.docentes.grados-academicos.store') }}" class="flex flex-wrap items-end gap-3 border-t border-gray-800 pt-5">
                @csrf
                <div class="min-w-[160px] flex-1">
                    <label class="admin-form-label" for="abreviatura-nueva">Nueva abreviatura</label>
                    <input
                        id="abreviatura-nueva"
                        name="abreviatura"
                        type="text"
                        class="admin-form-input font-mono"
                        value="{{ old('grado_edit_id') ? '' : old('abreviatura') }}"
                        placeholder="Dr."
                        required
                    >
                    @if (! old('grado_edit_id'))
                        @error('abreviatura')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
                    @endif
                </div>
                <button type="submit" class="btn-cean-primary text-sm">Agregar</button>
            </form>

            @if ($grados->isEmpty())
                <p class="text-sm text-gray-400">No hay grados registrados. Agrega el primero arriba.</p>
            @else
                <div class="overflow-x-auto rounded-xl border border-gray-700">
                    <table class="min-w-full divide-y divide-gray-800 text-sm">
                        <thead class="bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Abreviatura</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Docentes</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-400">Estado</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-400">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach ($grados as $grado)
                                <tr class="hover:bg-gray-800/30">
                                    <td class="px-4 py-3 font-mono text-cean-cyan">{{ $grado->abreviatura }}</td>
                                    <td class="px-4 py-3 text-gray-400">{{ $grado->docentes_count }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $grado->activo ? 'bg-green-900/40 text-green-300' : 'bg-gray-700 text-gray-400' }}">
                                            {{ $grado->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button
                                            type="button"
                                            class="text-xs font-medium text-cean-cyan hover:underline"
                                            @click="abrirEditarGrado(@js([
                                                'id' => $grado->id,
                                                'abreviatura' => $grado->abreviatura,
                                                'activo' => $grado->activo,
                                                'docentes_count' => $grado->docentes_count,
                                            ]))"
                                        >
                                            Editar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Modal: editar grado --}}
        <div x-show="modalEditarGrado" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="modalEditarGrado = false"></div>
            <div class="admin-modal relative w-full max-w-md" @click.stop x-show="gradoEditando">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Editar grado</h3>
                        <p class="mt-1 font-mono text-sm text-gray-400" x-text="gradoEditando?.abreviatura"></p>
                    </div>
                    <button type="button" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-800 hover:text-white" @click="modalEditarGrado = false" aria-label="Cerrar">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form
                    method="POST"
                    x-bind:action="gradoEditando ? '{{ url('/admin/docentes/grados-academicos') }}/' + gradoEditando.id : '#'"
                    class="space-y-4"
                >
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="grado_edit_id" x-bind:value="gradoEditando?.id">
                    <input type="hidden" name="docentes_count" x-bind:value="gradoEditando?.docentes_count ?? 0">

                    <div>
                        <label class="admin-form-label" for="abreviatura-editar">Abreviatura <span class="text-cean-red">*</span></label>
                        <input
                            id="abreviatura-editar"
                            name="abreviatura"
                            type="text"
                            class="admin-form-input font-mono"
                            x-model="gradoEditando.abreviatura"
                            required
                        >
                        @if (old('grado_edit_id'))
                            @error('abreviatura')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror
                        @endif
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-300">
                        <input type="hidden" name="activo" value="0">
                        <input
                            type="checkbox"
                            name="activo"
                            value="1"
                            class="rounded border-gray-600 bg-gray-800 text-cean-cyan"
                            x-model="gradoEditando.activo"
                        >
                        Grado activo (disponible al registrar docentes)
                    </label>

                    <div class="flex justify-end gap-3 border-t border-gray-800 pt-4">
                        <button type="button" class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800" @click="modalEditarGrado = false">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-cean-primary">Guardar</button>
                    </div>
                </form>

                <form
                    method="POST"
                    x-bind:action="gradoEditando ? '{{ url('/admin/docentes/grados-academicos') }}/' + gradoEditando.id : '#'"
                    class="mt-4 border-t border-gray-800 pt-4"
                    onsubmit="return confirm('¿Eliminar este grado académico?');"
                >
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm text-rose-400 hover:underline">Eliminar grado</button>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
