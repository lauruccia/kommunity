<?php

namespace Tests\Feature\Auth;

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
