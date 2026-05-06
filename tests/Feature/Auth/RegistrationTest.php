<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_legacy_user_gets_referral_code_when_link_is_generated(): void
    {
        $user = User::factory()->create([
            'name' => 'Kommunity Admin',
        ]);
        $user->forceFill(['referral_code' => null])->saveQuietly();

        $url = $user->referralRegistrationUrl();

        $this->assertSame('/register?ref=kommunityadmin', $url);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'referral_code' => 'kommunityadmin',
        ]);
    }

    public function test_direct_registration_does_not_reuse_previous_referral(): void
    {
        $inviter = User::factory()->create([
            'name' => 'Kommunity Admin',
            'referral_code' => 'admin',
        ]);

        $this->get('/register?ref='.$inviter->referral_code)
            ->assertSee('Kommunity Admin')
            ->assertSee('Campo compilato automaticamente dal referral link ricevuto.');

        $this->get('/register')
            ->assertDontSee('Kommunity Admin')
            ->assertSee('Inserisci nome e cognome della persona che ti ha invitato.');
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+39 333 1234567',
            'invited_by_name' => 'Mario Rossi',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'invited_by_name' => 'Mario Rossi',
        ]);
        $this->assertDatabaseHas('member_profiles', [
            'phone' => '+39 333 1234567',
        ]);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registration_requires_phone_and_inviter_full_name(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'invited_by_name' => 'Mario',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['phone', 'invited_by_name']);
        $this->assertGuest();
    }
}
