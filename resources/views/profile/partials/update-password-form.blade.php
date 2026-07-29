<section>
    <header>
        <h2 class="text-lg font-semibold text-white">Contraseña</h2>
        <p class="mt-1 text-sm text-gray-400">
            Usa una contraseña segura y única para proteger tu cuenta.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="admin-form-label">Contraseña actual</label>
            <input
                id="update_password_current_password"
                name="current_password"
                type="password"
                class="admin-form-input"
                autocomplete="current-password"
            >
            <x-input-error class="mt-1.5 text-xs text-cean-red" :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div>
            <label for="update_password_password" class="admin-form-label">Nueva contraseña</label>
            <input
                id="update_password_password"
                name="password"
                type="password"
                class="admin-form-input"
                autocomplete="new-password"
            >
            <x-input-error class="mt-1.5 text-xs text-cean-red" :messages="$errors->updatePassword->get('password')" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="admin-form-label">Confirmar contraseña</label>
            <input
                id="update_password_password_confirmation"
                name="password_confirmation"
                type="password"
                class="admin-form-input"
                autocomplete="new-password"
            >
            <x-input-error class="mt-1.5 text-xs text-cean-red" :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <button type="submit" class="btn-cean-primary !w-auto px-6">Actualizar contraseña</button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm text-green-300"
                >Contraseña actualizada.</p>
            @endif
        </div>
    </form>
</section>
