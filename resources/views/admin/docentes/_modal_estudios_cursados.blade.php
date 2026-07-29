<div
    x-show="modalEstudios"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
>
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="modalEstudios = false; estudioEditando = null"></div>
    <div class="admin-modal relative flex max-h-[90vh] w-full max-w-2xl flex-col" @click.stop>
        <div class="mb-5 flex shrink-0 items-start justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-white">Estudios cursados</h3>
                <p class="mt-1 text-sm text-gray-400">{{ $docente->nombreConGrado() }}</p>
            </div>
            <button type="button" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-800 hover:text-white" @click="modalEstudios = false; estudioEditando = null" aria-label="Cerrar">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="min-h-0 flex-1 space-y-5 overflow-y-auto pr-1">
            <form
                method="POST"
                action="{{ route('admin.docentes.estudios-cursados.store', $docente) }}"
                class="space-y-3 rounded-xl border border-gray-700 bg-gray-800/30 p-4"
            >
                @csrf
                <p class="text-sm font-medium text-gray-300">Agregar estudio</p>
                <div>
                    <label class="admin-form-label" for="descripcion-nueva">Descripción <span class="text-cean-red">*</span></label>
                    <input id="descripcion-nueva" name="descripcion" type="text" class="admin-form-input" value="{{ old('descripcion') }}" required>
                    @if (session('estudios_modal') && ! old('estudio_edit_id'))@error('descripcion')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="admin-form-label" for="documento-nuevo">Documento probatorio</label>
                        <input id="documento-nuevo" name="documento_probatorio" type="text" class="admin-form-input" value="{{ old('documento_probatorio') }}" placeholder="Ej. Cédula profesional">
                        @if (session('estudios_modal') && ! old('estudio_edit_id'))@error('documento_probatorio')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                    </div>
                    <div>
                        <label class="admin-form-label" for="fecha-nueva">Fecha <span class="text-cean-red">*</span></label>
                        <input id="fecha-nueva" name="fecha" type="date" class="admin-form-input" value="{{ old('fecha') }}" required>
                        @if (session('estudios_modal') && ! old('estudio_edit_id'))@error('fecha')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="btn-cean-primary text-sm">Agregar estudio</button>
                </div>
            </form>

            <div>
                <p class="mb-3 text-sm font-medium text-gray-300">Registrados</p>
                <template x-if="estudios.length === 0">
                    <p class="text-sm text-gray-500">No hay estudios registrados.</p>
                </template>
                <div class="space-y-3">
                    <template x-for="estudio in estudios" :key="estudio.id">
                        <div class="rounded-xl border border-gray-700 bg-gray-800/20 p-4">
                            <div x-show="estudioEditando?.id !== estudio.id">
                                <p class="font-medium text-white" x-text="estudio.descripcion"></p>
                                <dl class="mt-2 grid gap-1 text-xs sm:grid-cols-2">
                                    <div class="flex gap-2">
                                        <dt class="text-gray-500">Documento:</dt>
                                        <dd class="text-gray-300" x-text="estudio.documento_probatorio || '—'"></dd>
                                    </div>
                                    <div class="flex gap-2">
                                        <dt class="text-gray-500">Fecha:</dt>
                                        <dd class="text-gray-300" x-text="formatearFecha(estudio.fecha)"></dd>
                                    </div>
                                </dl>
                                <div class="mt-3 flex gap-3">
                                    <button type="button" class="text-xs font-medium text-cean-cyan hover:underline" @click="estudioEditando = { ...estudio }">
                                        Editar
                                    </button>
                                    <form
                                        method="POST"
                                        x-bind:action="'{{ url('/admin/docentes/'.$docente->id.'/estudios-cursados') }}/' + estudio.id"
                                        onsubmit="return confirm('¿Eliminar este estudio cursado?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-rose-400 hover:underline">Eliminar</button>
                                    </form>
                                </div>
                            </div>

                            <form
                                x-show="estudioEditando?.id === estudio.id"
                                method="POST"
                                x-bind:action="'{{ url('/admin/docentes/'.$docente->id.'/estudios-cursados') }}/' + estudio.id"
                                class="space-y-3"
                            >
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="estudio_edit_id" x-bind:value="estudio.id">
                                <div>
                                    <label class="admin-form-label">Descripción <span class="text-cean-red">*</span></label>
                                    <input name="descripcion" type="text" class="admin-form-input" x-model="estudioEditando.descripcion" required>
                                    @if (old('estudio_edit_id'))@error('descripcion')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="admin-form-label">Documento probatorio</label>
                                        <input name="documento_probatorio" type="text" class="admin-form-input" x-model="estudioEditando.documento_probatorio">
                                    </div>
                                    <div>
                                        <label class="admin-form-label">Fecha <span class="text-cean-red">*</span></label>
                                        <input name="fecha" type="date" class="admin-form-input" x-model="estudioEditando.fecha" required>
                                        @if (old('estudio_edit_id'))@error('fecha')<p class="mt-1.5 text-xs text-cean-red">{{ $message }}</p>@enderror @endif
                                    </div>
                                </div>
                                <div class="flex justify-end gap-2">
                                    <button type="button" class="rounded-lg border border-gray-600 px-3 py-1.5 text-xs font-medium text-gray-300 hover:bg-gray-800" @click="estudioEditando = null">
                                        Cancelar
                                    </button>
                                    <button type="submit" class="btn-cean-primary text-xs">Guardar</button>
                                </div>
                            </form>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
