<?php

namespace App\Http\Controllers;

use App\Enums\EventAttendanceStatus;
use App\Enums\EventType;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\City;
use App\Models\Event;
use App\Models\EventInvitation;
use App\Models\Profession;
use App\Models\Region;
use App\Models\User;
use App\Notifications\EventInvitationNotification;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    // ── INDEX ────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $user              = $request->user();
        $managedChapterIds = $this->managedChapterIds($user);
        $canManage         = $this->canManageEvents($user);

        // Modalità vista
        $viewModeParam = $request->string('view')->toString();
        $viewMode      = in_array($viewModeParam, ['month', 'week', 'day', 'list'], true) ? $viewModeParam : 'month';

        // Data selezionata
        $selectedDayParam = $request->string('day')->toString();
        $focusDate        = $selectedDayParam !== ''
            ? $this->parseDateParam($selectedDayParam, 'Y-m-d', now())
            : now();

        // Mese
        $selectedMonth = $request->string('month')->toString();
        $monthDate     = $selectedMonth !== ''
            ? $this->parseDateParam($selectedMonth, 'Y-m', $focusDate->copy()->startOfMonth())
            : $focusDate->copy()->startOfMonth();

        if ($selectedDayParam === '' && $selectedMonth === '') {
            $nextUpcomingFocusEvent = Event::query()
                ->where('is_published', true)
                ->where('status', '!=', 'cancelled')
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->first(['id', 'starts_at']);

            if ($nextUpcomingFocusEvent) {
                $focusDate = $nextUpcomingFocusEvent->starts_at->copy();
                $monthDate = $focusDate->copy()->startOfMonth();
            }
        }

        // Range calendario
        $monthStart    = $monthDate->copy()->startOfMonth();
        $monthEnd      = $monthDate->copy()->endOfMonth();
        $calendarStart = $monthStart->copy()->startOfWeek(CarbonInterface::MONDAY);
        $calendarEnd   = $monthEnd->copy()->endOfWeek(CarbonInterface::SUNDAY);
        $weekStart     = $focusDate->copy()->startOfWeek(CarbonInterface::MONDAY);
        $weekEnd       = $focusDate->copy()->endOfWeek(CarbonInterface::SUNDAY);
        $dayStart      = $focusDate->copy()->startOfDay();
        $dayEnd        = $focusDate->copy()->endOfDay();
        $queryStart    = collect([$calendarStart, $weekStart, $dayStart])->sort()->first();
        $queryEnd      = collect([$calendarEnd, $weekEnd, $dayEnd])->sort()->last();

        // Query eventi
        $calendarEvents = Event::query()
            ->with([
                'chapter',
                'organizer',
                'attendees' => fn ($q) => $q
                    ->select('users.id', 'users.name', 'users.email'),
            ])
            ->withCount([
                'registrations as attending_count'      => fn ($q) => $q->whereIn('status', EventAttendanceStatus::attendingValues()),
                'registrations as interested_count'     => fn ($q) => $q->where('status', EventAttendanceStatus::Interested->value),
                'registrations as not_interested_count' => fn ($q) => $q->where('status', EventAttendanceStatus::NotInterested->value),
            ])
            ->where('is_published', true)
            ->whereBetween('starts_at', [$queryStart, $queryEnd])
            ->orderBy('starts_at')
            ->get();

        $futureCalendarEvents = $calendarEvents
            ->filter(fn (Event $event) => ! $event->starts_at->isPast() && $event->status !== 'cancelled')
            ->values();

        $visibleCalendarEvents = $futureCalendarEvents->isNotEmpty()
            ? $futureCalendarEvents
            : $calendarEvents;

        // Griglia mese
        $calendarDays = collect(CarbonPeriod::create($calendarStart, $calendarEnd))
            ->map(fn (Carbon $day) => [
                'date'   => $day->copy(),
                'events' => $visibleCalendarEvents->filter(fn (Event $e) => $e->starts_at->isSameDay($day))->values(),
            ])->values();

        $calendarWeeks     = $calendarDays->chunk(7)->values();
        $selectedDay       = $focusDate->copy();
        $selectedDayEvents = $visibleCalendarEvents->filter(fn (Event $e) => $e->starts_at->isSameDay($selectedDay))->values();

        // Vista settimana
        $weekDays = collect(CarbonPeriod::create($weekStart, $weekEnd))
            ->map(fn (Carbon $day) => [
                'date'   => $day->copy(),
                'events' => $visibleCalendarEvents->filter(fn (Event $e) => $e->starts_at->isSameDay($day))->values(),
            ])->values();

        // Stato partecipazione utente
        $eventStatuses = DB::table('event_registrations')
            ->where('user_id', $user->id)
            ->pluck('status', 'event_id');

        $pendingInvitationEventIds = Schema::hasTable('event_invitations')
            ? EventInvitation::query()
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->pluck('event_id')
            : collect();

        // Dati per form creazione / inviti
        $managedChapters = Chapter::query()
            ->when(! $this->isAdmin($user), fn ($q) => $q->whereIn('id', $managedChapterIds))
            ->orderBy('name')
            ->get();

        $professions = $canManage ? Profession::where('is_active', true)->orderBy('name')->get(['id', 'name']) : collect();
        $categories  = $canManage ? Category::whereNull('parent_id')->where('is_active', true)->orderBy('name')->get(['id', 'name']) : collect();
        $regions     = $canManage ? Region::orderBy('name')->get(['id', 'name']) : collect();
        $cities      = $canManage ? City::orderBy('name')->get(['id', 'name']) : collect();

        $allUsers = $canManage
            ? User::with('memberProfile:id,user_id,company_name')
                ->where('id', '!=', $user->id)
                ->orderBy('name')
                ->get(['id', 'name', 'email'])
                ->map(fn ($u) => [
                    'id'    => $u->id,
                    'label' => $u->name . ($u->memberProfile?->company_name ? ' — ' . $u->memberProfile->company_name : ''),
                ])
            : collect();

        $detailEvents = $visibleCalendarEvents;

        $futureRegisteredEvent = Event::query()
            ->with(['chapter', 'organizer', 'attendees' => fn ($q) => $q->select('users.id', 'users.name', 'users.email')])
            ->withCount([
                'registrations as attending_count'      => fn ($q) => $q->whereIn('status', EventAttendanceStatus::attendingValues()),
                'registrations as interested_count'     => fn ($q) => $q->where('status', EventAttendanceStatus::Interested->value),
                'registrations as not_interested_count' => fn ($q) => $q->where('status', EventAttendanceStatus::NotInterested->value),
            ])
            ->where('is_published', true)
            ->where('status', '!=', 'cancelled')
            ->where('starts_at', '>=', now())
            ->whereHas('registrations', fn ($q) => $q
                ->where('user_id', $user->id)
                ->whereIn('status', EventAttendanceStatus::attendingValues()))
            ->orderBy('starts_at')
            ->first();

        $futureInvitedEvent = $pendingInvitationEventIds->isNotEmpty()
            ? Event::query()
                ->with(['chapter', 'organizer', 'attendees' => fn ($q) => $q->select('users.id', 'users.name', 'users.email')])
                ->withCount([
                    'registrations as attending_count'      => fn ($q) => $q->whereIn('status', EventAttendanceStatus::attendingValues()),
                    'registrations as interested_count'     => fn ($q) => $q->where('status', EventAttendanceStatus::Interested->value),
                    'registrations as not_interested_count' => fn ($q) => $q->where('status', EventAttendanceStatus::NotInterested->value),
                ])
                ->whereIn('id', $pendingInvitationEventIds)
                ->where('is_published', true)
                ->where('status', '!=', 'cancelled')
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->first()
            : null;

        $nextFutureEvent = Event::query()
            ->with(['chapter', 'organizer', 'attendees' => fn ($q) => $q->select('users.id', 'users.name', 'users.email')])
            ->withCount([
                'registrations as attending_count'      => fn ($q) => $q->whereIn('status', EventAttendanceStatus::attendingValues()),
                'registrations as interested_count'     => fn ($q) => $q->where('status', EventAttendanceStatus::Interested->value),
                'registrations as not_interested_count' => fn ($q) => $q->where('status', EventAttendanceStatus::NotInterested->value),
            ])
            ->where('is_published', true)
            ->where('status', '!=', 'cancelled')
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->first();

        $defaultDetailEvent = $futureRegisteredEvent
            ?? $futureInvitedEvent
            ?? $nextFutureEvent
            ?? $detailEvents->sortByDesc('starts_at')->first();

        if ($defaultDetailEvent && ! $detailEvents->contains('id', $defaultDetailEvent->id)) {
            $detailEvents = $detailEvents->push($defaultDetailEvent)->unique('id')->values();
        }

        $defaultDetailEventId = $defaultDetailEvent?->id;

        // Quick-events JSON per pannello dettaglio
        $quickEvents = $detailEvents->mapWithKeys(function (Event $event) use ($eventStatuses, $pendingInvitationEventIds, $user, $managedChapterIds) {
            $canManageThis = $this->isAdmin($user)
                || $user->can('gestire-eventi')
                || in_array($event->chapter_id, $managedChapterIds, true);

            return [
                $event->id => [
                    'id'                   => $event->id,
                    'title'                => $event->title,
                    'description'          => Str::limit((string) $event->description, 240),
                    'date_label'           => $event->starts_at->translatedFormat('d F Y'),
                    'time_label'           => $event->starts_at->format('H:i') . ($event->ends_at ? ' - ' . $event->ends_at->format('H:i') : ''),
                    'location'             => $event->location ?: 'Online',
                    'meeting_url'          => $event->meeting_url,
                    'chapter'              => $event->chapter?->name ?? 'Evento community',
                    'organizer_name'       => $event->organizer?->name ?? 'N/D',
                    'cover_image'          => $event->coverImageUrl(),
                    'type'                 => $event->type->value,
                    'type_label'           => $event->type->label(),
                    'event_status'         => $event->status,
                    'is_cancelled'         => $event->status === 'cancelled',
                    'is_past'              => $event->starts_at->isPast(),
                    'is_mine'              => $event->organizer_id === $user->id,
                    'can_manage'           => $canManageThis,
                    'attending_count'      => $event->attending_count,
                    'interested_count'     => $event->interested_count,
                    'not_interested_count' => $event->not_interested_count,
                    'capacity'             => $event->capacity,
                    'user_status'          => $eventStatuses[$event->id] ?? null,
                    'is_invited'            => $pendingInvitationEventIds->contains($event->id),
                    'attendees_preview'    => $event->attendees
                        ->sortBy(fn ($u) => match ($u->pivot->status) {
                            'attending', 'registered' => 0,
                            'interested'              => 1,
                            default                   => 2,
                        })
                        ->take(5)
                        ->map(fn ($u) => [
                            'name'     => $u->name,
                            'email'    => $u->email,
                            'status'   => $u->pivot->status,
                            'initials' => strtoupper(
                                collect(explode(' ', trim($u->name)))
                                    ->filter()
                                    ->map(fn ($w) => mb_substr($w, 0, 1))
                                    ->take(2)
                                    ->join('')
                            ),
                        ])->values()->all(),
                    'detail_url'           => route('events.show', $event),
                    'register_url'         => route('events.register', $event),
                    'unregister_url'       => route('events.unregister', $event),
                    'cancel_url'           => route('events.cancel', $event),
                ],
            ];
        });

        return view('events.index', [
            'viewMode'         => $viewMode,
            'calendarWeeks'    => $calendarWeeks,
            'monthDate'        => $monthDate,
            'selectedDay'      => $selectedDay,
            'selectedDayEvents' => $selectedDayEvents,
            'weekStart'        => $weekStart,
            'weekEnd'          => $weekEnd,
            'weekDays'         => $weekDays,
            'eventStatuses'    => $eventStatuses,
            'canManageEvents'  => $canManage,
            'managedChapters'  => $managedChapters,
            'eventTypes'       => EventType::options(),
            'quickEvents'      => $quickEvents,
            'defaultEventId'    => $defaultDetailEventId,
            'professions'      => $professions,
            'categories'       => $categories,
            'regions'          => $regions,
            'cities'           => $cities,
            'allUsers'         => $allUsers,
        ]);
    }

    // ── SHOW ─────────────────────────────────────────────────────────────────

    public function show(Event $event): View
    {
        abort_unless($event->is_published, 404);

        $event->load(['chapter', 'organizer', 'registrations.user.memberProfile']);

        $currentRegistration = auth()->user()?->eventRegistrations()
            ->where('event_id', $event->id)->first();

        $registrationStats = [
            EventAttendanceStatus::Interested->value    => $event->registrations->where('status', EventAttendanceStatus::Interested->value)->count(),
            EventAttendanceStatus::NotInterested->value => $event->registrations->where('status', EventAttendanceStatus::NotInterested->value)->count(),
            EventAttendanceStatus::Attending->value     => $event->registrations->whereIn('status', EventAttendanceStatus::attendingValues())->count(),
        ];

        $canManageEvent = $this->canManageEvent(auth()->user(), $event);

        $professions = $canManageEvent ? Profession::where('is_active', true)->orderBy('name')->get(['id', 'name']) : collect();
        $categories  = $canManageEvent ? Category::whereNull('parent_id')->where('is_active', true)->orderBy('name')->get(['id', 'name']) : collect();
        $regions     = $canManageEvent ? Region::orderBy('name')->get(['id', 'name']) : collect();
        $cities      = $canManageEvent ? City::orderBy('name')->get(['id', 'name']) : collect();
        $managedChapters = $canManageEvent
            ? Chapter::query()
                ->when(! $this->isAdmin(auth()->user()), fn ($q) => $q->whereIn('id', $this->managedChapterIds(auth()->user())))
                ->orderBy('name')->get()
            : collect();

        $allUsers = $canManageEvent
            ? User::with('memberProfile:id,user_id,company_name')
                ->where('id', '!=', auth()->id())
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($u) => [
                    'id'    => $u->id,
                    'label' => $u->name . ($u->memberProfile?->company_name ? ' — ' . $u->memberProfile->company_name : ''),
                ])
            : collect();

        $invitationCount = $canManageEvent ? $event->invitations()->count() : 0;

        return view('events.show', [
            'event'               => $event,
            'currentRegistration' => $currentRegistration,
            'registrationStats'   => $registrationStats,
            'attendanceOptions'   => EventAttendanceStatus::options(),
            'canManageEvent'      => $canManageEvent,
            'professions'         => $professions,
            'categories'          => $categories,
            'regions'             => $regions,
            'cities'              => $cities,
            'allUsers'            => $allUsers,
            'invitationCount'     => $invitationCount,
            'managedChapters'     => $managedChapters,
        ]);
    }

    // ── STORE ────────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->canManageEvents($request->user()), 403);

        $managedChapterIds = $this->managedChapterIds($request->user());
        $chapterRule = $this->isAdmin($request->user())
            ? Rule::exists('chapters', 'id')
            : Rule::in($managedChapterIds);

        $validated = $request->validate([
            'chapter_id'           => ['required', $chapterRule],
            'title'                => ['required', 'string', 'max:255'],
            'description'          => ['nullable', 'string'],
            'cover_image'          => ['nullable', 'image', 'max:4096'],
            'type'                 => ['required', Rule::in(array_keys(EventType::options()))],
            'starts_at'            => ['required', 'date'],
            'ends_at'              => ['nullable', 'date', 'after_or_equal:starts_at'],
            'location'             => ['nullable', 'string', 'max:255'],
            'meeting_url'          => ['nullable', 'url', 'max:255'],
            'capacity'             => ['nullable', 'integer', 'min:1'],
            'is_published'         => ['nullable', 'boolean'],
            'invite_target'        => ['nullable', 'string', Rule::in(['none', 'all', 'chapter', 'profession', 'category', 'city', 'region', 'users'])],
            'invite_chapter_id'    => ['nullable', Rule::exists('chapters', 'id')],
            'invite_profession_id' => ['nullable', Rule::exists('professions', 'id')],
            'invite_category_id'   => ['nullable', Rule::exists('categories', 'id')],
            'invite_city_id'       => ['nullable', Rule::exists('cities', 'id')],
            'invite_region_id'     => ['nullable', Rule::exists('regions', 'id')],
            'invite_user_ids'      => ['nullable', 'array'],
            'invite_user_ids.*'    => ['integer', Rule::exists('users', 'id')],
        ]);

        // Upload cover image
        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('events/covers', 'public');
        }

        $event = Event::create([
            'chapter_id'   => $validated['chapter_id'],
            'organizer_id' => $request->user()->id,
            'title'        => $validated['title'],
            'slug'         => Str::slug($validated['title'] . '-' . Str::random(5)),
            'description'  => $validated['description'] ?? null,
            'cover_image'  => $coverImagePath,
            'type'         => $validated['type'],
            'starts_at'    => $validated['starts_at'],
            'ends_at'      => $validated['ends_at'] ?? null,
            'location'     => $validated['location'] ?? null,
            'meeting_url'  => $validated['meeting_url'] ?? null,
            'capacity'     => $validated['capacity'] ?? null,
            'status'       => ($validated['is_published'] ?? false) ? 'published' : 'draft',
            'is_published' => (bool) ($validated['is_published'] ?? false),
        ]);

        // Processa inviti
        $inviteTarget = $validated['invite_target'] ?? 'none';
        if ($inviteTarget !== 'none') {
            $this->processInvitations($event, $request->user(), $inviteTarget, $validated);
        }

        return redirect()->route('events.show', $event)->with('status', 'event-created');
    }

    // ── INVITE (per eventi esistenti) ────────────────────────────────────────

    public function invite(Request $request, Event $event): RedirectResponse
    {
        abort_unless($this->canManageEvent($request->user(), $event), 403);

        $validated = $request->validate([
            'invite_target'        => ['required', Rule::in(['all', 'chapter', 'profession', 'category', 'city', 'region', 'users'])],
            'invite_chapter_id'    => ['nullable', Rule::exists('chapters', 'id')],
            'invite_profession_id' => ['nullable', Rule::exists('professions', 'id')],
            'invite_category_id'   => ['nullable', Rule::exists('categories', 'id')],
            'invite_city_id'       => ['nullable', Rule::exists('cities', 'id')],
            'invite_region_id'     => ['nullable', Rule::exists('regions', 'id')],
            'invite_user_ids'      => ['nullable', 'array'],
            'invite_user_ids.*'    => ['integer', Rule::exists('users', 'id')],
        ]);

        $count = $this->processInvitations($event, $request->user(), $validated['invite_target'], $validated);

        return back()->with('status', 'invitations-sent')->with('invite_count', $count);
    }

    // ── REGISTER / UNREGISTER ────────────────────────────────────────────────

    public function register(Request $request, Event $event): RedirectResponse
    {
        abort_unless($event->is_published, 404);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(EventAttendanceStatus::options()))],
        ]);

        $status = $validated['status'];

        $flashStatus = DB::transaction(function () use ($event, $request, $status): string {
            $lockedEvent = Event::query()->whereKey($event->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedEvent->is_published, 404);

            $existing = $lockedEvent->registrations()
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()->first();

            $isAttendingStatus = in_array($status, EventAttendanceStatus::attendingValues(), true);
            $wasAttending      = $existing && in_array($existing->status, EventAttendanceStatus::attendingValues(), true);

            if (
                $isAttendingStatus && ! $wasAttending
                && $lockedEvent->capacity !== null
                && $lockedEvent->attendingRegistrations()->count() >= $lockedEvent->capacity
            ) {
                return 'event-full';
            }

            $lockedEvent->attendees()->syncWithoutDetaching([
                $request->user()->id => ['status' => $status, 'registered_at' => now()],
            ]);

            // Segna invito come accettato
            EventInvitation::where('event_id', $event->id)
                ->where('user_id', $request->user()->id)
                ->whereNull('accepted_at')
                ->update(['status' => 'accepted', 'accepted_at' => now()]);

            return 'event-response-updated';
        });

        return back()->with('status', $flashStatus);
    }

    public function unregister(Request $request, Event $event): RedirectResponse
    {
        $event->attendees()->detach($request->user()->id);

        return back()->with('status', 'event-unregistered');
    }

    // ── CANCEL ───────────────────────────────────────────────────────────────

    public function cancel(Request $request, Event $event): RedirectResponse
    {
        abort_unless($this->canManageEvent($request->user(), $event), 403);

        $event->update(['status' => 'cancelled']);

        return back()->with('status', 'event-cancelled');
    }

    // ── HELPERS ──────────────────────────────────────────────────────────────

    /**
     * Risolve gli utenti da invitare, crea i record EventInvitation e invia le notifiche.
     * Ritorna il numero di notifiche inviate con successo.
     */
    protected function processInvitations(Event $event, $inviter, string $target, array $data): int
    {
        $excludeId = $inviter->id;

        /** @var \Illuminate\Support\Collection $users */
        $users = match ($target) {
            'all' => User::where('id', '!=', $excludeId)->get(['id', 'name', 'email']),

            'chapter' => User::whereHas('memberProfile', fn ($q) =>
                    $q->where('chapter_id', $data['invite_chapter_id'] ?? 0)
                )->where('id', '!=', $excludeId)->get(['id', 'name', 'email']),

            'profession' => User::whereHas('memberProfile', fn ($q) =>
                    $q->where('profession_id', $data['invite_profession_id'] ?? 0)
                )->where('id', '!=', $excludeId)->get(['id', 'name', 'email']),

            'category' => User::whereHas('memberProfile', function ($q) use ($data) {
                    $catId      = $data['invite_category_id'] ?? 0;
                    $profileIds = DB::table('member_profile_category')
                        ->where('category_id', $catId)->pluck('member_profile_id');
                    $q->whereIn('id', $profileIds);
                })->where('id', '!=', $excludeId)->get(['id', 'name', 'email']),

            'city' => User::whereHas('memberProfile', fn ($q) =>
                    $q->where('city_id', $data['invite_city_id'] ?? 0)
                )->where('id', '!=', $excludeId)->get(['id', 'name', 'email']),

            'region' => User::whereHas('memberProfile', fn ($q) =>
                    $q->where('region_id', $data['invite_region_id'] ?? 0)
                )->where('id', '!=', $excludeId)->get(['id', 'name', 'email']),

            'users' => User::whereIn('id', (array) ($data['invite_user_ids'] ?? []))
                ->where('id', '!=', $excludeId)->get(['id', 'name', 'email']),

            default => collect(),
        };

        $count = 0;

        foreach ($users as $user) {
            $invitation = EventInvitation::firstOrCreate(
                ['event_id' => $event->id, 'user_id' => $user->id],
                [
                    'invited_by' => $inviter->id,
                    'token'      => Str::random(64),
                    'status'     => 'pending',
                ]
            );

            if (! $invitation->notified_at) {
                try {
                    $user->notify(new EventInvitationNotification($event, $inviter));
                    $invitation->update(['notified_at' => now()]);
                    $count++;
                } catch (\Throwable) {
                    // Non blocca: l'invito è già salvato, si potrà reinviare
                }
            }
        }

        return $count;
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
        return Chapter::query()->where('leader_id', $user->id)->pluck('id')->all();
    }

    private function parseDateParam(string $value, string $format, Carbon $fallback): Carbon
    {
        try {
            $date = Carbon::createFromFormat($format, $value, config('app.timezone'));
            return $date->format($format) === $value ? $date : $fallback;
        } catch (\Throwable) {
            return $fallback;
        }
    }
}
