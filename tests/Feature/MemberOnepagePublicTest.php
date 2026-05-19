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
