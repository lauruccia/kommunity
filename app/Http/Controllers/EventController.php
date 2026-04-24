<?php

namespace App\Http\Controllers;

use App\Enums\EventAttendanceStatus;
use App\Enums\EventType;
use App\Models\Chapter;
use App\Models\Event;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $managedChapterIds = $this->managedChapterIds($user);
        $viewMode = in_array($request->string('view')->toString(), ['month', 'week', 'day'], true)
            ? $request->string('view')->toString()
            : 'month';
        $selectedDayParam = $request->string('day')->toString();
        $focusDate = $selectedDayParam !== ''
            ? Carbon::createFromFormat('Y-m-d', $selectedDayParam, config('app.timezone'))
            : now();
        $selectedMonth = $request->string('month')->toString();
        $monthDate = $selectedMonth !== ''
            ? Carbon::createFromFormat('Y-m', $selectedMonth, config('app.timezone'))
            : $focusDate->copy()->startOfMonth();
        $monthStart = $monthDate->copy()->startOfMonth();
        $monthEnd = $monthDate->copy()->endOfMonth();
        $calendarStart = $monthStart->copy()->startOfWeek(CarbonInterface::MONDAY);
        $calendarEnd = $monthEnd->copy()->endOfWeek(CarbonInterface::SUNDAY);
        $weekStart = $focusDate->copy()->startOfWeek(CarbonInterface::MONDAY);
        $weekEnd = $focusDate->copy()->endOfWeek(CarbonInterface::SUNDAY);
        $dayStart = $focusDate->copy()->startOfDay();
        $dayEnd = $focusDate->copy()->endOfDay();
        $queryStart = collect([$calendarStart, $weekStart, $dayStart])->sort()->first();
        $queryEnd = collect([$calendarEnd, $weekEnd, $dayEnd])->sort()->last();

        $calendarEvents = Event::query()
            ->with(['chapter', 'organizer'])
            ->withCount([
                'registrations as attending_count' => fn ($query) => $query->whereIn('status', EventAttendanceStatus::attendingValues()),
            ])
            ->where('is_published', true)
            ->whereBetween('starts_at', [$queryStart, $queryEnd])
            ->orderBy('starts_at')
            ->get();

        $calendarDays = collect(CarbonPeriod::create($calendarStart, $calendarEnd))
            ->map(function (Carbon $day) use ($calendarEvents): array {
                return [
                    'date' => $day->copy(),
                    'in_current_month' => $day->month === $day->copy()->startOfMonth()->month,
                    'events' => $calendarEvents
                        ->filter(fn (Event $event) => $event->starts_at->isSameDay($day))
                        ->values(),
                ];
            })
            ->values();

        $calendarWeeks = $calendarDays->chunk(7)->values();
        $selectedDay = $focusDate->copy();

        $selectedDayEvents = $calendarEvents
            ->filter(fn (Event $event) => $event->starts_at->isSameDay($selectedDay))
            ->values();

        $weekDays = collect(CarbonPeriod::create($weekStart, $weekEnd))
            ->map(fn (Carbon $day) => [
                'date' => $day->copy(),
                'events' => $calendarEvents
                    ->filter(fn (Event $event) => $event->starts_at->isSameDay($day))
                    ->values(),
            ])
            ->values();

        $upcomingEvents = Event::query()
            ->with(['chapter', 'organizer'])
            ->withCount([
                'registrations as attending_count' => fn ($query) => $query->whereIn('status', EventAttendanceStatus::attendingValues()),
            ])
            ->where('is_published', true)
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->limit(6)
            ->get();

        $eventStatuses = DB::table('event_registrations')
            ->where('user_id', $user->id)
            ->pluck('status', 'event_id');

        return view('events.index', [
            'viewMode' => $viewMode,
            'calendarWeeks' => $calendarWeeks,
            'monthDate' => $monthDate,
            'selectedDay' => $selectedDay,
            'selectedDayEvents' => $selectedDayEvents,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'weekDays' => $weekDays,
            'upcomingEvents' => $upcomingEvents,
            'eventStatuses' => $eventStatuses,
            'canManageEvents' => $this->canManageEvents($user),
            'managedChapters' => Chapter::query()
                ->when(! $this->isAdmin($user), fn ($query) => $query->whereIn('id', $managedChapterIds))
                ->orderBy('name')
                ->get(),
            'eventTypes' => EventType::options(),
            'quickEvents' => $calendarEvents->mapWithKeys(function (Event $event) use ($eventStatuses) {
                $status = $eventStatuses[$event->id] ?? null;

                return [
                    $event->id => [
                        'id' => $event->id,
                        'title' => $event->title,
                        'description' => Str::limit((string) $event->description, 180),
                        'date_label' => $event->starts_at->translatedFormat('d F Y'),
                        'time_label' => $event->starts_at->format('H:i').($event->ends_at ? ' - '.$event->ends_at->format('H:i') : ''),
                        'location' => $event->location ?: 'Online',
                        'chapter' => $event->chapter?->name ?? 'Evento community',
                        'detail_url' => route('events.show', $event),
                        'register_url' => route('events.register', $event),
                        'unregister_url' => route('events.unregister', $event),
                        'status' => $status,
                    ],
                ];
            }),
        ]);
    }

    public function show(Event $event): View
    {
        abort_unless($event->is_published, 404);

        $event->load([
            'chapter',
            'organizer',
            'registrations.user.memberProfile',
        ]);

        $currentRegistration = auth()->user()?->eventRegistrations()
            ->where('event_id', $event->id)
            ->first();

        $registrationStats = [
            EventAttendanceStatus::Interested->value => $event->registrations->where('status', EventAttendanceStatus::Interested->value)->count(),
            EventAttendanceStatus::NotInterested->value => $event->registrations->where('status', EventAttendanceStatus::NotInterested->value)->count(),
            EventAttendanceStatus::Attending->value => $event->registrations->whereIn('status', EventAttendanceStatus::attendingValues())->count(),
        ];

        return view('events.show', [
            'event' => $event,
            'currentRegistration' => $currentRegistration,
            'registrationStats' => $registrationStats,
            'attendanceOptions' => EventAttendanceStatus::options(),
            'canManageEvent' => $this->canManageEvent(auth()->user(), $event),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->canManageEvents($request->user()), 403);

        $managedChapterIds = $this->managedChapterIds($request->user());
        $chapterRule = $this->isAdmin($request->user())
            ? Rule::exists('chapters', 'id')
            : Rule::in($managedChapterIds);

        $validated = $request->validate([
            'chapter_id' => ['required', $chapterRule],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(array_keys(EventType::options()))],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_url' => ['nullable', 'url', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        Event::create([
            'chapter_id' => $validated['chapter_id'],
            'organizer_id' => $request->user()->id,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title'].'-'.Str::random(5)),
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'] ?? null,
            'location' => $validated['location'] ?? null,
            'meeting_url' => $validated['meeting_url'] ?? null,
            'capacity' => $validated['capacity'] ?? null,
            'status' => ($validated['is_published'] ?? false) ? 'published' : 'draft',
            'is_published' => (bool) ($validated['is_published'] ?? false),
        ]);

        return back()->with('status', 'event-created');
    }

    public function register(Request $request, Event $event): RedirectResponse
    {
        abort_unless($event->is_published, 404);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(EventAttendanceStatus::options()))],
        ]);

        $status = $validated['status'];

        if (
            $status === EventAttendanceStatus::Attending->value
            && $event->capacity !== null
            && ! $event->registrations()->where('user_id', $request->user()->id)->exists()
            && $event->attendingRegistrations()->count() >= $event->capacity
        ) {
            return back()->with('status', 'event-full');
        }

        $existing = $event->registrations()->where('user_id', $request->user()->id)->first();

        if (
            $status === EventAttendanceStatus::Attending->value
            && $event->capacity !== null
            && $existing?->status !== EventAttendanceStatus::Attending->value
            && $existing?->status !== EventAttendanceStatus::Registered->value
            && $event->attendingRegistrations()->count() >= $event->capacity
        ) {
            return back()->with('status', 'event-full');
        }

        $event->attendees()->syncWithoutDetaching([
            $request->user()->id => [
                'status' => $status,
                'registered_at' => now(),
            ],
        ]);

        return back()->with('status', 'event-response-updated');
    }

    public function unregister(Request $request, Event $event): RedirectResponse
    {
        $event->attendees()->detach($request->user()->id);

        return back()->with('status', 'event-unregistered');
    }

    protected function canManageEvents($user): bool
    {
        return $this->isAdmin($user)
            || ($user?->hasRole('leader-capitolo') && ! empty($this->managedChapterIds($user)))
            || $user?->can('gestire-eventi');
    }

    protected function canManageEvent($user, Event $event): bool
    {
        if (! $user) {
            return false;
        }

        if ($this->isAdmin($user) || $user->can('gestire-eventi')) {
            return true;
        }

        return in_array($event->chapter_id, $this->managedChapterIds($user), true);
    }

    protected function isAdmin($user): bool
    {
        return $user?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    protected function managedChapterIds($user): array
    {
        if (! $user) {
            return [];
        }

        return Chapter::query()
            ->where('leader_id', $user->id)
            ->pluck('id')
            ->all();
    }
}
