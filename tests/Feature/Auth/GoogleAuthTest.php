<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_always_shows_google_option(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Continuar con Google', false)
            ->assertSee('auth/google/redirect', false);
    }

    public function test_google_redirect_shows_error_when_not_configured(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        $this->get('/auth/google/redirect')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }

    public function test_google_redirect_route_is_registered(): void
    {
        $this->assertNotNull(route('auth.google.redirect'));
        $this->assertNotNull(route('auth.google.callback'));
    }
}
