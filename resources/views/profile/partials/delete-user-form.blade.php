<section x-data="{ modalEliminar: @js($errors->userDeletion->isNotEmpty()) }">
    <header>
        <h2 class="text-lg font-semibold text-white">Eliminar cuenta</h2>
        <p class="mt-1 text-sm text-gray-400">
            Al eliminar tu cuenta se borrarán tus datos de forma permanente. Esta acción no se puede deshacer.
        </p>
    </header>

    <button
        type="button"
        class="mt-6 rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-500"
        @click="modalEliminar = true"
    >
        Eliminar cuenta
    </button>

    <div
        x-show="modalEliminar"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="modal-eliminar-cuenta-titulo"
        @keydown.escape.window="modalEliminar = false"
    >
        <div
            class="absolute inset-0 bg-black/70 backdrop-blur-sm"
            @click="modalEliminar = false"
        ></div>

        <div class="admin-modal relative w-full max-w-md" @click.stop>
            <h3 id="modal-eliminar-cuenta-titulo" class="text-lg font-semibold text-white">
                ¿Eliminar tu cuenta?
            </h3>
            <p class="mt-2 text-sm text-gray-400">
                Ingresa tu contraseña para confirmar que deseas eliminar tu cuenta de forma permanente.
            </p>

            <form method="post" action="{{ route('profile.destroy') }}" class="mt-5 space-y-4">
                @csrf
                @method('delete')

                <div>
                    <label for="password" class="admin-form-label">Contraseña</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="admin-form-input"
                        placeholder="Tu contraseña"
                        required
                    >
                    <x-input-error class="mt-1.5 text-xs text-cean-red" :messages="$errors->userDeletion->get('password')" />
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        class="rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800"
                        @click="modalEliminar = false"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-500"
                    >
                        Sí, eliminar cuenta
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
