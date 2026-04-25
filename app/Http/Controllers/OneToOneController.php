<?php

namespace App\Http\Controllers;

use App\Enums\OneToOneStatus;
use App\Models\AvailabilitySlot;
use App\Models\OneToOneFollowup;
use App\Models\OneToOneNote;
use App\Models\OneToOneRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OneToOneController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'member' => ['nullable', 'exists:users,id'],
            'request' => ['nullable', 'exists:one_to_one_requests,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', Rule::in(['sent', 'received'])],
            'status' => ['nullable', Rule::in(array_column(OneToOneStatus::cases(), 'value'))],
            'meeting_mode' => ['nullable', Rule::in(['online', 'in_person'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $selectedMemberId = $filters['member'] ?? null;

        $requestsQuery = OneToOneRequest::query()
            ->with([
                'requester.memberProfile.city',
                'recipient.memberProfile.city',
                'recipient.availabilitySlots',
                'notes' => fn ($query) => $query->where('user_id', $request->user()->id)->where('type', 'private'),
                'followUps' => fn ($query) => $query->latest('id'),
            ])
            ->where(fn ($query) => $query
                ->where('requester_id', $request->user()->id)
                ->orWhere('recipient_id', $request->user()->id));

        if ($selectedMemberId) {
            $requestsQuery->where(function ($query) use ($request, $selectedMemberId): void {
                $query
                    ->where(function ($nestedQuery) use ($request, $selectedMemberId): void {
                        $nestedQuery
                            ->where('requester_id', $request->user()->id)
                            ->where('recipient_id', $selectedMemberId);
                    })
                    ->orWhere(function ($nestedQuery) use ($request, $selectedMemberId): void {
                        $nestedQuery
                            ->where('requester_id', $selectedMemberId)
                            ->where('recipient_id', $request->user()->id);
                    });
            });
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $requestsQuery->where(function ($query) use ($search): void {
                $query
                    ->whereHas('requester', fn ($userQuery) => $userQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%'))
                    ->orWhereHas('recipient', fn ($userQuery) => $userQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%'))
                    ->orWhere('goal', 'like', '%'.$search.'%');
            });
        }

        if (($filters['type'] ?? null) === 'sent') {
            $requestsQuery->where('requester_id', $request->user()->id);
        }

        if (($filters['type'] ?? null) === 'received') {
            $requestsQuery->where('recipient_id', $request->user()->id);
        }

        if (! empty($filters['status'])) {
            $requestsQuery->where('status', $filters['status']);
        }

        if (! empty($filters['meeting_mode'])) {
            $requestsQuery->where('meeting_mode', $filters['meeting_mode']);
        }

        if (! empty($filters['date_from'])) {
            $requestsQuery->whereDate('requested_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $requestsQuery->whereDate('requested_at', '<=', $filters['date_to']);
        }

        $statsQuery = clone $requestsQuery;

        $paginatedRequests = $requestsQuery
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $selectedRequest = null;

        if (! empty($filters['request'])) {
            $selectedRequest = $paginatedRequests->getCollection()->firstWhere('id', (int) $filters['request']);

            if (! $selectedRequest) {
                $selectedRequest = OneToOneRequest::query()
                    ->with([
                        'requester.memberProfile.city',
                        'recipient.memberProfile.city',
                        'notes' => fn ($query) => $query->where('user_id', $request->user()->id)->where('type', 'private'),
                        'followUps' => fn ($query) => $query->latest('id'),
                    ])
                    ->whereKey($filters['request'])
                    ->where(function ($query) use ($request): void {
                        $query
                            ->where('requester_id', $request->user()->id)
                            ->orWhere('recipient_id', $request->user()->id);
                    })
                    ->first();
            }
        }

        return view('one-to-ones.index', [
            'requests' => $paginatedRequests,
            'selectedRequest' => $selectedRequest,
            'members' => User::query()
                ->with(['memberProfile.city', 'availabilitySlots' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('weekday')
                    ->orderBy('starts_at')])
                ->whereKeyNot($request->user()->id)
                ->whereHas('memberProfile', fn ($query) => $query->where('is_active', true))
                ->orderBy('name')
                ->get(),
            'selectedMember' => $selectedMemberId ? User::query()->with('memberProfile.city')->find($selectedMemberId) : null,
            'filters' => $filters,
            'summary' => [
                'total' => (clone $statsQuery)->count(),
                'received' => (clone $statsQuery)->where('recipient_id', $request->user()->id)->count(),
                'sent' => (clone $statsQuery)->where('requester_id', $request->user()->id)->count(),
            ],
            'statusOptions' => OneToOneStatus::options(),
            'typeOptions' => [
                'sent' => 'Inviate da me',
                'received' => 'Ricevute da me',
            ],
            'modeOptions' => [
                'online' => 'Online',
                'in_person' => 'In presenza',
            ],
            'availabilitySlots' => AvailabilitySlot::query()
                ->where('user_id', $request->user()->id)
                ->orderBy('weekday')
                ->orderBy('starts_at')
                ->get(),
            'weekdayOptions' => [
                1 => 'Lunedi',
                2 => 'Martedi',
                3 => 'Mercoledi',
                4 => 'Giovedi',
                5 => 'Venerdi',
                6 => 'Sabato',
                7 => 'Domenica',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'recipient_id' => ['required', 'exists:users,id', 'different:'. $request->user()->id],
            'requested_at' => ['nullable', 'date'],
            'meeting_mode' => ['required', Rule::in(['online', 'in_person'])],
            'meeting_link' => ['nullable', 'url'],
            'meeting_location' => ['nullable', 'string', 'max:255'],
            'goal' => ['required', 'string', 'max:1000'],
            'pre_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $requestedAt = ! empty($data['requested_at']) ? Carbon::parse($data['requested_at']) : null;
        $matchingSlot = null;
        $isBookable = false;

        if ($requestedAt) {
            $matchingSlot = AvailabilitySlot::query()
                ->where('user_id', $data['recipient_id'])
                ->where('is_active', true)
                ->where('meeting_mode', $data['meeting_mode'])
                ->where('weekday', (int) $requestedAt->isoWeekday())
                ->where('starts_at', '<=', $requestedAt->format('H:i:s'))
                ->where('ends_at', '>', $requestedAt->format('H:i:s'))
                ->orderBy('starts_at')
                ->first();

            $hasConflict = OneToOneRequest::query()
                ->where(function ($query) use ($request, $data): void {
                    $query
                        ->where('recipient_id', $data['recipient_id'])
                        ->orWhere('requester_id', $data['recipient_id'])
                        ->orWhere('recipient_id', $request->user()->id)
                        ->orWhere('requester_id', $request->user()->id);
                })
                ->whereIn('status', [OneToOneStatus::Accepted->value, OneToOneStatus::Rescheduled->value])
                ->where('requested_at', $requestedAt)
                ->exists();

            $isBookable = $matchingSlot !== null && ! $hasConflict;
        }

        OneToOneRequest::query()->create([
            'requester_id' => $request->user()->id,
            'recipient_id' => $data['recipient_id'],
            'availability_slot_id' => $matchingSlot?->id,
            'requested_at' => $requestedAt ?? now()->addDays(2),
            'meeting_mode' => $data['meeting_mode'],
            'meeting_link' => $data['meeting_link'] ?? null,
            'meeting_location' => $data['meeting_location'] ?? null,
            'goal' => $data['goal'],
            'pre_notes' => $data['pre_notes'] ?? null,
            'status' => $isBookable ? OneToOneStatus::Accepted : OneToOneStatus::Pending,
        ]);

        $redirectTo = $request->input('redirect_to', 'back');

        if ($redirectTo === 'directory') {
            return redirect()->route('directory.index')
                ->with('success', $isBookable
                    ? 'Richiesta One-to-one confermata! Trovala nella sezione One-to-one.'
                    : 'Richiesta One-to-one inviata! Trovala nella sezione One-to-one.');
        }

        return redirect()->route('one-to-ones.index')
            ->with('status', $isBookable ? 'one-to-one-booked' : 'one-to-one-created');
    }

    public function updateStatus(Request $request, OneToOneRequest $oneToOneRequest): RedirectResponse
    {
        abort_unless($oneToOneRequest->recipient_id === $request->user()->id || $oneToOneRequest->requester_id === $request->user()->id, 403);

        $data = $request->validate([
            'status' => ['nullable', Rule::in(array_column(OneToOneStatus::cases(), 'value'))],
            'post_notes' => ['nullable', 'string', 'max:2000'],
            'follow_up_notes' => ['nullable', 'string', 'max:2000'],
            'private_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = $request->user();
        $isRecipient = $oneToOneRequest->recipient_id === $user->id;
        $isRequester = $oneToOneRequest->requester_id === $user->id;

        if ($isRecipient) {
            $oneToOneRequest->fill([
                'status' => $data['status'] ?? $oneToOneRequest->status->value,
                'post_notes' => $data['post_notes'] ?? null,
            ])->save();
        }

        if ($isRequester && ($data['status'] ?? null) === OneToOneStatus::Cancelled->value) {
            $oneToOneRequest->fill(['status' => OneToOneStatus::Cancelled])->save();
        }

        if ($isRequester && array_key_exists('follow_up_notes', $data)) {
            $followUpContent = trim((string) ($data['follow_up_notes'] ?? ''));

            if ($followUpContent === '') {
                $oneToOneRequest->followUps()->delete();
            } else {
                OneToOneFollowup::query()->updateOrCreate(
                    ['one_to_one_request_id' => $oneToOneRequest->id],
                    ['content' => $followUpContent, 'follow_up_at' => now()]
                );
            }
        }

        if (array_key_exists('private_note', $data)) {
            $privateNote = trim((string) ($data['private_note'] ?? ''));

            if ($privateNote === '') {
                OneToOneNote::query()
                    ->where('one_to_one_request_id', $oneToOneRequest->id)
                    ->where('user_id', $user->id)
                    ->where('type', 'private')
                    ->delete();
            } else {
                OneToOneNote::query()->updateOrCreate(
                    [
                        'one_to_one_request_id' => $oneToOneRequest->id,
                        'user_id' => $user->id,
                        'type' => 'private',
                    ],
                    ['note' => $privateNote]
                );
            }
        }

        return back()->with('status', 'one-to-one-updated');
    }

    public function storeAvailability(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'weekday' => ['required', 'integer', 'between:1,7'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
            'meeting_mode' => ['required', Rule::in(['online', 'in_person'])],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        AvailabilitySlot::query()->create([
            ...$data,
            'user_id' => $request->user()->id,
            'timezone' => config('app.timezone', 'Europe/Rome'),
            'is_active' => true,
        ]);

        return back()->with('status', 'availability-created');
    }

    public function destroyAvailability(Request $request, AvailabilitySlot $availabilitySlot): RedirectResponse
    {
        abort_unless($availabilitySlot->user_id === $request->user()->id, 403);

        $availabilitySlot->delete();

        return back()->with('status', 'availability-deleted');
    }
}
