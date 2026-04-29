<?php

namespace Tests\Feature;

use App\Enums\EventAttendanceStatus;
use App\Enums\EventType;
use App\Models\Chapter;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_events_page_renders_calendar_actions_and_three_column_shell(): void
    {
        $user = User::factory()->create();
        $organizer = User::factory()->create(['name' => 'Kommunity Admin']);
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
        $response->assertSee(route('events.index', ['view' => 'week', 'month' => now()->format('Y-m'), 'day' => $event->starts_at->format('Y-m-d')], false), false);
        $response->assertSee(route('events.index', ['view' => 'day', 'month' => now()->format('Y-m'), 'day' => $event->starts_at->format('Y-m-d')], false), false);
        $response->assertSee(route('events.index', ['view' => 'list', 'month' => now()->format('Y-m'), 'day' => $event->starts_at->format('Y-m-d')], false), false);
        $response->assertSee(route('events.register', $event, false), false);
        $response->assertDontSee('application-logo', false);
    }

    public function test_event_response_actions_update_registration_state(): void
    {
        $user = User::factory()->create();
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
}
