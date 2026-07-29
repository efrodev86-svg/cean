<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\GoogleOAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GoogleAuthController extends Controller
{
    public function redirect(): SymfonyRedirectResponse|RedirectResponse
    {
        if (! GoogleOAuth::isConfigured()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'El inicio de sesión con Google no está disponible. Usa correo y contraseña o contacta a control escolar.']);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        if (! GoogleOAuth::isConfigured()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'El inicio de sesión con Google no está disponible. Usa correo y contraseña o contacta a control escolar.']);
        }

        $googleUser = Socialite::driver('google')->user();
        $allowedDomain = config('services.google.allowed_domain');

        if ($allowedDomain && ! Str::endsWith(Str::lower($googleUser->getEmail()), '@'.Str::lower($allowedDomain))) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Usa tu cuenta institucional de Google Workspace.']);
        }

        $user = User::query()->where('email', $googleUser->getEmail())->first();

        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Tu cuenta no está registrada en '.config('cean.acronym').' ('.config('cean.full_name').'). Contacta a control escolar.']);
        }

        if (! $user->docenteEstaActivo() && $user->role === User::ROLE_DOCENTE) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Tu cuenta de docente está deshabilitada. Contacta a control escolar.']);
        }

        if (! $user->alumnoEstaActivo() && $user->isAlumno()) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Tu cuenta de alumno está deshabilitada. Contacta a control escolar.']);
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->intended($user->homeRoute());
    }
}
