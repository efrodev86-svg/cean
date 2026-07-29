<?php

namespace Tests\Feature;

use App\Models\Sede;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_encargado_profile_only_allows_password_change(): void
    {
        $sede = Sede::query()->create(['nombre' => 'Sede Centro', 'clave' => 'CCT-CEN']);
        $user = User::factory()->create([
            'name' => 'Encargado Prueba',
            'email' => 'encargado@ensq.edu.mx',
            'role' => User::ROLE_ENCARGADO,
            'sede_id' => $sede->id,
        ]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Datos de acceso', false)
            ->assertSee('Solo puedes cambiar tu contraseña', false)
            ->assertSee('Contraseña', false)
            ->assertDontSee('Información de la cuenta', false)
            ->assertDontSee('Guardar cambios', false)
            ->assertDontSee('Eliminar cuenta', false);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Otro Nombre',
                'email' => 'otro@ensq.edu.mx',
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('profile.destroy'), [
                'password' => 'password',
            ])
            ->assertForbidden();

        $user->refresh();

        $this->assertSame('Encargado Prueba', $user->name);
        $this->assertSame('encargado@ensq.edu.mx', $user->email);
        $this->assertNotNull($user->fresh());
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
