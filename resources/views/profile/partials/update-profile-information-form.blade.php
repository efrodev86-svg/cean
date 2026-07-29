<section>
    <header>
        <h2 class="text-lg font-semibold text-white">Información de la cuenta</h2>
        <p class="mt-1 text-sm text-gray-400">
            Actualiza tu nombre y correo electrónico.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-4">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="admin-form-label">Nombre</label>
            <input
                id="name"
                name="name"
                type="text"
                class="admin-form-input"
                value="{{ old('name', $user->name) }}"
                required
                autofocus
                autocomplete="name"
            >
            <x-input-error class="mt-1.5 text-xs text-cean-red" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="admin-form-label">Correo electrónico</label>
            <input
                id="email"
                name="email"
                type="email"
                class="admin-form-input"
                value="{{ old('email', $user->email) }}"
                required
                autocomplete="username"
            >
            <x-input-error class="mt-1.5 text-xs text-cean-red" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 rounded-lg border border-amber-700/50 bg-amber-900/20 px-4 py-3 text-sm text-amber-200">
                    <p>Tu correo no está verificado.</p>
                    <button
                        form="send-verification"
                        class="mt-2 text-cean-cyan underline-offset-2 hover:underline"
                    >
                        Reenviar correo de verificación
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-green-300">
                            Se envió un nuevo enlace de verificación a tu correo.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <button type="submit" class="btn-cean-primary !w-auto px-6">Guardar cambios</button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm text-green-300"
                >Cambios guardados.</p>
            @endif
        </div>
    </form>
</section>
