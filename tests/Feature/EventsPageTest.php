<?php

namespace Tests\Feature;

use App\Enums\EventAttendanceStatus;
use App\Enums\EventType;
use App\Models\Chapter;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_events_page_renders_calendar_actions_and_three_column_shell(): void
    {
        $user = User::factory()->create();
        $this->makeEventUserReady($user);
        $organizer = User::factory()->create(['name' => 'Kommunity Admin']);
        $this->makeEventUserReady($organizer);
        $chapter = Chapter::query()->create([
            'name' => 'Kapitolo Roma',
            'slug' => 'kapitolo-roma',
            'leader_id' => $organizer->id,
            'is_active' => true,
        ]);
        $event = Event::query()->create([
            'chapter_id' => $chapter->id,
            'organizer_id' => $organizer->id,
            'title' => 'Workshop Innovazione',
            'slug' => 'workshop-innovazione',
            'description' => 'Evento demo per il calendario.',
            'type' => EventType::Networking,
            'starts_at' => now()->startOfMonth()->addDays(10)->setTime(10, 0),
            'ends_at' => now()->startOfMonth()->addDays(10)->setTime(12, 30),
            'location' => 'Roma, Via del Corso 123',
            'capacity' => 20,
            'status' => 'published',
            'is_published' => true,
        ]);

        $response = $this->actingAs($user)->get(route('events.index', [
            'view' => 'month',
            'month' => now()->format('Y-m'),
            'day' => $event->starts_at->format('Y-m-d'),
        ]));

        $response->assertOk();
        $response->assertSee('km-events-layout', false);
        $response->assertSee('Workshop Innovazione');
        $response->assertSee('Cerca evento...', false);
        $response->assertSee('Filtri');
        $response->assertSee('Settimana');
        $response->assertSee('Giorno');
        $response->assertSee('Lista');
        $response->assertDontSee('application-logo', false);
    }

    public function test_event_response_actions_update_registration_state(): void
    {
        $user = User::factory()->create();
        $this->makeEventUserReady($user);
        $event = Event::query()->create([
            'title' => 'Business Matching Roma',
            'slug' => 'business-matching-roma-' . Str::random(5),
            'type' => EventType::BusinessMatching,
            'starts_at' => now()->addDays(5)->setTime(17, 30),
            'ends_at' => now()->addDays(5)->setTime(19, 0),
            'status' => 'published',
            'is_published' => true,
        ]);

        $this->actingAs($user)->post(route('events.register', $event), [
            'status' => EventAttendanceStatus::Attending->value,
        ])->assertRedirect();

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => EventAttendanceStatus::Attending->value,
        ]);

        $this->actingAs($user)->delete(route('events.unregister', $event))->assertRedirect();

        $this->assertDatabaseMissing('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_role_targeted_event_is_visible_only_to_target_role(): void
    {
        $leaderRole = Role::findOrCreate('leader-capitolo');
        $memberRole = Role::findOrCreate('membro');

        $leader = User::factory()->create();
        $leader->assignRole($leaderRole);
        $this->makeEventUserReady($leader);

        $member = User::factory()->create();
        $member->assignRole($memberRole);
        $this->makeEventUserReady($member);

        $event = Event::query()->create([
            'title' => 'Riunione leader pianeti',
            'slug' => 'riunione-leader-pianeti-' . Str::random(5),
            'type' => EventType::Networking,
            'starts_at' => now()->addDays(3)->setTime(18, 0),
            'status' => 'published',
            'is_published' => true,
            'audience_type' => 'by_role',
        ]);
        $event->targetRoles()->sync([$leaderRole->id]);

        $this->actingAs($leader)
            ->get(route('events.index', ['view' => 'list']))
            ->assertOk()
            ->assertSee('Riunione leader pianeti');

        $this->actingAs($member)
            ->get(route('events.index', ['view' => 'list']))
            ->assertOk()
            ->assertDontSee('Riunione leader pianeti');

        $this->actingAs($member)
            ->get(route('events.show', $event))
            ->assertNotFound();
    }

    private function makeEventUserReady(User $user): void
    {
        $user->memberProfile()->update([
            'onboarding_completed' => true,
            'is_active' => true,
            'status' => 'active',
        ]);
    }
}
