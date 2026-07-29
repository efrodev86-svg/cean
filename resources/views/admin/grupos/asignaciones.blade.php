<x-admin-layout title="Asignaciones del grupo" breadcrumb="Asignaciones">
    <div
        class="mx-auto max-w-5xl space-y-6"
        x-data="{
            docentes: @js($docentesOpciones),
            filas: @js($filas),
            get asignadas() {
                return this.filas.filter((fila) => fila.docente_id).length;
            },
            get progreso() {
                return this.filas.length > 0 ? Math.round((this.asignadas / this.filas.length) * 100) : 0;
            },
            normalizar(texto) {
                return String(texto || '')
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .trim();
            },
            filtrar(consulta) {
                const q = this.normalizar(consulta);
                if (! q) {
                    return this.docentes.slice(0, 8);
                }

                return this.docentes
                    .filter((docente) => this.normalizar(docente.nombre).includes(q))
                    .slice(0, 8);
            },
            seleccionar(indice, docente) {
                this.filas[indice].docente_id = docente.id;
                this.filas[indice].etiqueta = docente.nombre;
                this.filas[indice].abierto = false;
                this.filas[indice].destacado = -1;
            },
            limpiar(indice) {
                this.filas[indice].docente_id = null;
                this.filas[indice].etiqueta = '';
                this.filas[indice].abierto = false;
                this.filas[indice].destacado = -1;
            },
            alEscribir(indice) {
                const fila = this.filas[indice];
                fila.abierto = true;
                fila.destacado = 0;

                if (! fila.etiqueta.trim()) {
                    fila.docente_id = null;
                    return;
                }

                const exacto = this.docentes.find(
                    (docente) => this.normalizar(docente.nombre) === this.normalizar(fila.etiqueta)
                );

                fila.docente_id = exacto ? exacto.id : null;
            },
            alEnfocar(indice) {
                this.filas[indice].abierto = true;
                if (this.filas[indice].destacado < 0) {
                    this.filas[indice].destacado = 0;
                }
            },
            alSalir(indice) {
                setTimeout(() => {
                    this.filas[indice].abierto = false;
                    const fila = this.filas[indice];
                    if (! fila.docente_id && fila.etiqueta.trim()) {
                        fila.etiqueta = '';
                    } else if (fila.docente_id) {
                        const docente = this.docentes.find((item) => item.id === fila.docente_id);
                        if (docente) {
                            fila.etiqueta = docente.nombre;
                        }
                    }
                }, 150);
            },
            navegar(indice, direccion) {
                const opciones = this.filtrar(this.filas[indice].etiqueta);
                if (! opciones.length) {
                    return;
                }

                this.filas[indice].abierto = true;
                let actual = this.filas[indice].destacado ?? -1;
                actual = (actual + direccion + opciones.length) % opciones.length;
                this.filas[indice].destacado = actual;
            },
            confirmar(indice) {
                const opciones = this.filtrar(this.filas[indice].etiqueta);
                const destacado = this.filas[indice].destacado ?? 0;
                if (opciones[destacado]) {
                    this.seleccionar(indice, opciones[destacado]);
                }
            },
        }"
    >
        @if (session('success'))
            <div class="admin-alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="admin-alert-error" role="alert">
                <p class="font-medium">No se pudieron guardar las asignaciones. Corrige lo siguiente:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <a href="{{ route('admin.grupos', array_filter(['ciclo' => $grupo->ciclo_escolar_id, 'sede' => $grupo->sede_id])) }}" class="inline-flex items-center gap-1.5 text-sm text-gray-400 transition hover:text-cean-cyan">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                Volver a grupos
            </a>
        </div>

        <div class="admin-panel border-l-4 border-cean-cyan">
            <h2 class="text-lg font-semibold text-white">Asignaciones — {{ $grupo->etiqueta() }}</h2>
            <p class="mt-1 text-sm text-gray-400">
                Ciclo {{ $grupo->cicloEscolar?->nombre ?? '—' }}
                · {{ $grupo->sede?->nombre ?? '—' }}
                · {{ $grupo->alumnos_count }} alumno(s)
            </p>
            <div class="mt-4">
                <div class="mb-1.5 flex items-center justify-between text-xs text-gray-400">
                    <span>Plantilla del grupo</span>
                    <span x-text="`${asignadas}/${filas.length} materias con docente`"></span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-gray-800">
                    <div
                        class="h-full rounded-full bg-cean-cyan transition-all"
                        :style="`width: ${progreso}%`"
                    ></div>
                </div>
            </div>
        </div>

        <div class="admin-panel">
            @if (count($filas) === 0)
                <p class="text-sm text-gray-400">
                    No hay materias en el plan para {{ $grupo->licenciatura }} · {{ $grupo->semestre }}° semestre.
                    Revisa el catálogo de materias.
                </p>
            @else
                <form method="POST" action="{{ route('admin.grupos.asignaciones.update', $grupo) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="overflow-x-auto rounded-xl border border-gray-700">
                        <table class="min-w-full divide-y divide-gray-800 text-sm">
                            <thead class="bg-gray-800/50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-400">Materia</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-400">Clave</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-400">Docente titular</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-400">Estatus</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800">
                                <template x-for="(fila, indice) in filas" :key="fila.materia_id">
                                    <tr class="hover:bg-gray-800/30">
                                        <td class="px-4 py-3 text-gray-200" x-text="fila.nombre"></td>
                                        <td class="px-4 py-3 font-mono text-xs text-cean-cyan" x-text="fila.clave"></td>
                                        <td class="px-4 py-3">
                                            <input type="hidden" :name="`asignaciones[${indice}][materia_id]`" :value="fila.materia_id">
                                            <input type="hidden" :name="`asignaciones[${indice}][docente_id]`" :value="fila.docente_id ?? ''">

                                            <div class="relative min-w-[16rem]">
                                                <div class="flex gap-1">
                                                    <input
                                                        type="text"
                                                        class="admin-form-input mt-0"
                                                        placeholder="Escribe para buscar docente…"
                                                        autocomplete="off"
                                                        x-model="fila.etiqueta"
                                                        @input="alEscribir(indice)"
                                                        @focus="alEnfocar(indice)"
                                                        @blur="alSalir(indice)"
                                                        @keydown.arrow-down.prevent="navegar(indice, 1)"
                                                        @keydown.arrow-up.prevent="navegar(indice, -1)"
                                                        @keydown.enter.prevent="confirmar(indice)"
                                                        @keydown.escape.prevent="fila.abierto = false"
                                                    >
                                                    <button
                                                        type="button"
                                                        class="rounded-lg border border-gray-700 px-2 text-xs text-gray-400 transition hover:border-cean-red/40 hover:text-cean-red"
                                                        x-show="fila.docente_id || fila.etiqueta"
                                                        x-cloak
                                                        @mousedown.prevent="limpiar(indice)"
                                                        title="Quitar docente"
                                                    >
                                                        ✕
                                                    </button>
                                                </div>

                                                <div
                                                    x-show="fila.abierto && filtrar(fila.etiqueta).length"
                                                    x-cloak
                                                    class="absolute z-20 mt-1 max-h-48 w-full overflow-auto rounded-lg border border-gray-700 bg-gray-900 shadow-xl"
                                                >
                                                    <template x-for="(docente, opcionIndice) in filtrar(fila.etiqueta)" :key="docente.id">
                                                        <button
                                                            type="button"
                                                            class="block w-full px-3 py-2 text-left text-sm text-gray-200 transition hover:bg-cean-cyan/15 hover:text-cean-cyan"
                                                            :class="fila.destacado === opcionIndice && 'bg-cean-cyan/15 text-cean-cyan'"
                                                            @mousedown.prevent="seleccionar(indice, docente)"
                                                            x-text="docente.nombre"
                                                        ></button>
                                                    </template>
                                                </div>

                                                <p
                                                    class="mt-1.5 text-xs text-amber-300"
                                                    x-show="fila.etiqueta.trim() && ! fila.docente_id"
                                                    x-cloak
                                                >
                                                    Selecciona un docente de la lista.
                                                </p>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase"
                                                :class="fila.docente_id ? 'bg-green-900/40 text-green-300' : 'bg-amber-900/40 text-amber-300'"
                                                x-text="fila.docente_id ? 'Completo' : 'Pendiente'"
                                            ></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    @foreach ($errors->getMessages() as $campo => $mensajes)
                        @if (str_starts_with($campo, 'asignaciones.'))
                            @foreach ($mensajes as $mensaje)
                                <p class="text-xs text-cean-red">{{ $mensaje }}</p>
                            @endforeach
                        @endif
                    @endforeach

                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-800 pt-4">
                        <a href="{{ route('admin.grupos.edit', $grupo) }}" class="text-sm text-gray-400 transition hover:text-cean-cyan">
                            Editar datos del grupo
                        </a>
                        <button type="submit" class="btn-cean-primary">Guardar asignaciones</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-admin-layout>
