<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberOnepagePublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_onepage_is_public_without_navigation_for_guests(): void
    {
        $user = User::factory()->create([
            'name' => 'Test Membro',
        ]);

        $response = $this->get(route('members.show', $user->memberOnepage->slug));

        $response->assertOk();
        $response->assertSee('Test Membro');
        $response->assertDontSee('Dashboard');
        $response->assertDontSee('One-to-one');
        $response->assertDontSee('Messaggi');
    }

    public function test_member_onepage_shows_profile_texts_when_profile_is_newer(): void
    {
        $user = User::factory()->create([
            'name' => 'Test Membro',
        ]);

        $user->memberOnepage->forceFill([
            'intro_text' => 'Bio breve vecchia',
            'about_text' => 'Bio vecchia',
            'services_text' => 'Servizi vecchi',
            'updated_at' => now()->subHour(),
        ])->saveQuietly();

        $user->memberProfile->forceFill([
            'short_bio' => 'Bio breve admin',
            'bio' => 'Bio admin',
            'services' => 'Servizi admin',
            'updated_at' => now(),
        ])->saveQuietly();

        $response = $this->get(route('members.show', $user->memberOnepage->slug));

        $response->assertOk();
        $response->assertSee('Bio breve admin');
        $response->assertSee('Bio admin');
        $response->assertSee('Servizi admin');
        $response->assertDontSee('Bio breve vecchia');
        $response->assertDontSee('Bio vecchia');
        $response->assertDontSee('Servizi vecchi');
    }

    public function test_faq_requires_login(): void
    {
        $this->get(route('faq'))->assertRedirect(route('login'));
    }

    public function test_logged_in_user_can_open_faq(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('faq'))
            ->assertOk()
            ->assertSee('FAQ Kommunity');
    }
}
