<?php

namespace App\Http\Controllers;

use App\Enums\OneToOneStatus;
use App\Models\AvailabilitySlot;
use App\Models\OneToOneFollowup;
use App\Models\OneToOneNote;
use App\Models\OneToOneRequest;
use App\Models\User;
use App\Notifications\OneToOneReceivedNotification;
use App\Notifications\OneToOneStatusChangedNotification;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OneToOneController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'member'       => ['nullable', 'exists:users,id'],
            'request'      => ['nullable', 'exists:one_to_one_requests,id'],
            'search'       => ['nullable', 'string', 'max:255'],
            'type'         => ['nullable', Rule::in(['sent', 'received'])],
            'status'       => ['nullable', Rule::in(array_column(OneToOneStatus::cases(), 'value'))],
            'meeting_mode' => ['nullable', Rule::in(['online', 'in_person'])],
            'date_from'    => ['nullable', 'date'],
            'date_to'      => ['nullable', 'date'],
            'compose'      => ['nullable', 'boolean'],
        ]);

        $selectedMemberId = $filters['member'] ?? null;

        $requestsQuery = OneToOneRequest::query()
            ->with([
                'requester.memberProfile.city',
                'recipient.memberProfile.city',
                'recipient.availabilitySlots',
                'notes'    => fn ($q) => $q->where('user_id', $request->user()->id)->where('type', 'private'),
                'followUps'=> fn ($q) => $q->latest('id'),
            ])
            ->where(fn ($q) => $q
                ->where('requester_id', $request->user()->id)
                ->orWhere('recipient_id', $request->user()->id));

        if ($selectedMemberId) {
            $requestsQuery->where(function ($q) use ($request, $selectedMemberId): void {
                $q->where(fn ($n) => $n->where('requester_id', $request->user()->id)->where('recipient_id', $selectedMemberId))
                  ->orWhere(fn ($n) => $n->where('requester_id', $selectedMemberId)->where('recipient_id', $request->user()->id));
            });
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $requestsQuery->where(function ($q) use ($search): void {
                $q->whereHas('requester', fn ($u) => $u->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%'))
                  ->orWhereHas('recipient', fn ($u) => $u->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%'))
                  ->orWhere('goal', 'like', '%'.$search.'%');
            });
        }

        if (($filters['type'] ?? null) === 'sent')     { $requestsQuery->where('requester_id', $request->user()->id); }
        if (($filters['type'] ?? null) === 'received') { $requestsQuery->where('recipient_id', $request->user()->id); }
        if (! empty($filters['status']))       { $requestsQuery->where('status', $filters['status']); }
        if (! empty($filters['meeting_mode'])) { $requestsQuery->where('meeting_mode', $filters['meeting_mode']); }
        if (! empty($filters['date_from']))    { $requestsQuery->whereDate('requested_at', '>=', $filters['date_from']); }
        if (! empty($filters['date_to']))      { $requestsQuery->whereDate('requested_at', '<=', $filters['date_to']); }

        $statsQuery = clone $requestsQuery;

        $paginatedRequests = $requestsQuery->latest()->paginate(10)->withQueryString();

        $selectedRequest = null;
        if (! empty($filters['request'])) {
            $selectedRequest = $paginatedRequests->getCollection()->firstWhere('id', (int) $filters['request']);
            if (! $selectedRequest) {
                $selectedRequest = OneToOneRequest::query()
                    ->with([
                        'requester.memberProfile.city',
                        'recipient.memberProfile.city',
                        'notes'    => fn ($q) => $q->where('user_id', $request->user()->id)->where('type', 'private'),
                        'followUps'=> fn ($q) => $q->latest('id'),
                    ])
                    ->whereKey($filters['request'])
                    ->where(fn ($q) => $q->where('requester_id', $request->user()->id)->orWhere('recipient_id', $request->user()->id))
                    ->first();
            }
        }

        return view('one-to-ones.index', [
            'requests'         => $paginatedRequests,
            'selectedRequest'  => $selectedRequest,
            'members'          => User::query()
                ->with(['memberProfile.city', 'availabilitySlots' => fn ($q) => $q->where('is_active', true)->orderBy('weekday')->orderBy('starts_at')])
                ->whereKeyNot($request->user()->id)
                ->whereHas('memberProfile', fn ($q) => $q->where('is_active', true))
                ->orderBy('name')
                ->get(),
            'selectedMember'   => $selectedMemberId ? User::query()->with('memberProfile.city')->find($selectedMemberId) : null,
            'filters'          => $filters,
            'summary'          => [
                'total'    => (clone $statsQuery)->count(),
                'received' => (clone $statsQuery)->where('recipient_id', $request->user()->id)->count(),
                'sent'     => (clone $statsQuery)->where('requester_id', $request->user()->id)->count(),
            ],
            'statusOptions'    => OneToOneStatus::options(),
            'typeOptions'      => ['sent' => 'Inviate da me', 'received' => 'Ricevute da me'],
            'modeOptions'      => ['online' => 'Online', 'in_person' => 'In presenza'],
            'availabilitySlots'=> AvailabilitySlot::query()
                ->where('user_id', $request->user()->id)
                ->orderBy('weekday')->orderBy('starts_at')
                ->get(),
            'weekdayOptions'   => [1=>'Lunedi',2=>'Martedi',3=>'Mercoledi',4=>'Giovedi',5=>'Venerdi',6=>'Sabato',7=>'Domenica'],
        ]);
    }

    /**
     * Crea nuova richiesta one-to-one.
     * FIX #2: dopo l'invio chiude il modale → redirect al pannello dettaglio.
     * FIX #3: requested_at è opzionale — se mancante rimane NULL (non forziamo now()+2d).
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'recipient_id'     => ['required', 'exists:users,id', 'different:'.$request->user()->id],
            'requested_at'     => ['nullable', 'date'],
            'meeting_mode'     => ['required', Rule::in(['online', 'in_person'])],
            'meeting_link'     => ['nullable', 'url', 'max:500'],
            'meeting_location' => ['nullable', 'string', 'max:255'],
            'goal'             => ['required', 'string', 'max:1000'],
            'pre_notes'        => ['nullable', 'string', 'max:2000'],
        ]);

        // FIX #3: data/ora facoltativa — null è valido, non sostituire con now()+2d
        $requestedAt  = filled($data['requested_at']) ? Carbon::parse($data['requested_at']) : null;
        $matchingSlot = null;
        $isBookable   = false;

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
                ->where(fn ($q) => $q
                    ->where('recipient_id', $data['recipient_id'])
                    ->orWhere('requester_id', $data['recipient_id'])
                    ->orWhere('recipient_id', $request->user()->id)
                    ->orWhere('requester_id', $request->user()->id))
                ->whereIn('status', [OneToOneStatus::Accepted->value, OneToOneStatus::Rescheduled->value])
                ->where('requested_at', $requestedAt)
                ->exists();

            $isBookable = $matchingSlot !== null && ! $hasConflict;
        }

        $oneToOneRequest = OneToOneRequest::query()->create([
            'requester_id'       => $request->user()->id,
            'recipient_id'       => $data['recipient_id'],
            'availability_slot_id' => $matchingSlot?->id,
            'requested_at'       => $requestedAt,          // FIX #3: null è valido
            'meeting_mode'       => $data['meeting_mode'],
            'meeting_link'       => $data['meeting_link'] ?? null,
            'meeting_location'   => $data['meeting_location'] ?? null,
            'goal'               => $data['goal'],
            'pre_notes'          => $data['pre_notes'] ?? null,
            'status'             => $isBookable ? OneToOneStatus::Accepted : OneToOneStatus::Pending,
        ]);

        $recipient = User::find($data['recipient_id']);
        $oneToOneRequest->load('requester');
        $recipient?->notify(new OneToOneReceivedNotification($oneToOneRequest));

        // Redirect directory
        if ($request->input('redirect_to') === 'directory') {
            return redirect()->route('directory.index')->with('success',
                $isBookable ? 'One-to-one prenotato! Trovala in Agenda relazionale.' : 'Richiesta inviata! Trovala in Agenda relazionale.');
        }

        // FIX #2: redirect al DETTAGLIO (non riaprire il modale) — mostra toast via session('success')
        $successMessage = $isBookable
            ? 'One-to-one prenotato! Lo slot è disponibile e senza conflitti.'
            : 'Richiesta inviata. Il destinatario riceverà una notifica.';

        return redirect()
            ->route('one-to-ones.index', ['request' => $oneToOneRequest->id])
            ->with('success', $successMessage);
    }

    /**
     * Aggiorna stato / note / link / proposta variazione orario.
     * FIX #5: stati corretti — solo il destinatario accetta/rifiuta/riprogramma.
     * FIX #6: chiunque dei due può proporre un nuovo orario → stato Rescheduled.
     */
    public function updateStatus(Request $request, OneToOneRequest $oneToOneRequest): RedirectResponse
    {
        $this->authorize('update', $oneToOneRequest);

        $data = $request->validate([
            'status'               => ['nullable', Rule::in(array_column(OneToOneStatus::cases(), 'value'))],
            'confirm_completed'    => ['nullable', 'boolean'],
            'post_notes'           => ['nullable', 'string', 'max:2000'],
            'follow_up_notes'      => ['nullable', 'string', 'max:2000'],
            'private_note'         => ['nullable', 'string', 'max:2000'],
            'meeting_link'         => ['nullable', 'url', 'max:500'],
            'meeting_location'     => ['nullable', 'string', 'max:255'],
            // FIX #6: proposta variazione orario
            'propose_new_datetime' => ['nullable', 'date'],
        ]);

        $user        = $request->user();
        $isRecipient = $oneToOneRequest->recipient_id === $user->id;
        $isRequester = $oneToOneRequest->requester_id === $user->id;
        $statusMsg   = 'one-to-one-updated';

        // ── Conferma completamento ────────────────────────────────────────────
        if ($request->boolean('confirm_completed')) {
            if (! $oneToOneRequest->canBeConfirmedBy($user->id)) {
                $reason = $oneToOneRequest->completionConfirmedBy($user->id)
                    ? 'Hai già confermato il completamento di questo incontro.'
                    : 'Non puoi confermare: l\'incontro deve essere in stato Accettato.';
                return back()->with('error', $reason);
            }
            if ($isRequester) { $oneToOneRequest->requester_completed_at = now(); }
            if ($isRecipient) { $oneToOneRequest->recipient_completed_at = now(); }
            if ($oneToOneRequest->isFullyConfirmed()) {
                $oneToOneRequest->status = OneToOneStatus::Completed;
                $oneToOneRequest->completed_at ??= now();
                $statusMsg = 'one-to-one-completed';
            } else {
                $statusMsg = 'one-to-one-completion-confirmed';
            }
            $oneToOneRequest->save();
        }

        // ── FIX #5: il destinatario accetta / rifiuta / passa a rischeduled ──
        if ($isRecipient && isset($data['status']) && in_array($data['status'], [
            OneToOneStatus::Accepted->value,
            OneToOneStatus::Declined->value,
            OneToOneStatus::Rescheduled->value,
        ], true)) {
            if (! in_array($oneToOneRequest->status, [OneToOneStatus::Pending, OneToOneStatus::Rescheduled], true)) {
                return back()->with('error', 'Non puoi modificare lo stato di un incontro già accettato o completato.');
            }
            $oneToOneRequest->status = OneToOneStatus::from($data['status']);
            $oneToOneRequest->save();

            $requester = User::find($oneToOneRequest->requester_id);
            $requester?->notify(new OneToOneStatusChangedNotification($oneToOneRequest, $oneToOneRequest->status, $user->name));
        }

        // ── Note post-incontro (solo destinatario) ───────────────────────────
        if ($isRecipient && array_key_exists('post_notes', $data)) {
            $oneToOneRequest->post_notes = $data['post_notes'];
            $oneToOneRequest->save();
        }

        // ── Il mittente annulla ───────────────────────────────────────────────
        if ($isRequester && ($data['status'] ?? null) === OneToOneStatus::Cancelled->value) {
            if ($oneToOneRequest->status === OneToOneStatus::Completed) {
                return back()->with('error', 'Non puoi annullare un incontro già completato.');
            }
            $oneToOneRequest->fill(['status' => OneToOneStatus::Cancelled])->save();
            $recipient = User::find($oneToOneRequest->recipient_id);
            $recipient?->notify(new OneToOneStatusChangedNotification($oneToOneRequest, OneToOneStatus::Cancelled, $user->name));
        }

        // ── Aggiorna link/luogo meeting (mittente) ───────────────────────────
        if ($isRequester) {
            if ($oneToOneRequest->meeting_mode === 'online' && array_key_exists('meeting_link', $data)) {
                $oneToOneRequest->meeting_link = $data['meeting_link'] ?: null;
                $oneToOneRequest->save();
            }
            if ($oneToOneRequest->meeting_mode === 'in_person' && array_key_exists('meeting_location', $data)) {
                $oneToOneRequest->meeting_location = $data['meeting_location'] ?: null;
                $oneToOneRequest->save();
            }
        }

        // ── FIX #6: proposta variazione orario (entrambe le parti) ──────────
        // Chiunque dei due può proporre un nuovo datetime → stato diventa Rescheduled
        // e l'altro riceve notifica per confermare/rifiutare.
        if (filled($data['propose_new_datetime'] ?? null)
            && ! in_array($oneToOneRequest->status, [OneToOneStatus::Completed, OneToOneStatus::Cancelled], true)
        ) {
            $newDt = Carbon::parse($data['propose_new_datetime']);
            $oneToOneRequest->requested_at = $newDt;
            $oneToOneRequest->status       = OneToOneStatus::Rescheduled;
            $oneToOneRequest->save();
            $statusMsg = 'one-to-one-rescheduled';

            // Notifica la controparte
            $otherUserId = $isRequester ? $oneToOneRequest->recipient_id : $oneToOneRequest->requester_id;
            $otherUser   = User::find($otherUserId);
            $otherUser?->notify(new OneToOneStatusChangedNotification($oneToOneRequest, OneToOneStatus::Rescheduled, $user->name));
        }

        // ── Follow-up notes (mittente) ────────────────────────────────────────
        if ($isRequester && array_key_exists('follow_up_notes', $data)) {
            $content = trim((string) ($data['follow_up_notes'] ?? ''));
            if ($content === '') {
                $oneToOneRequest->followUps()->delete();
            } else {
                OneToOneFollowup::query()->updateOrCreate(
                    ['one_to_one_request_id' => $oneToOneRequest->id],
                    ['content' => $content, 'follow_up_at' => now()]
                );
            }
        }

        // ── Nota privata (entrambi) ───────────────────────────────────────────
        if (array_key_exists('private_note', $data)) {
            $note = trim((string) ($data['private_note'] ?? ''));
            if ($note === '') {
                OneToOneNote::query()
                    ->where('one_to_one_request_id', $oneToOneRequest->id)
                    ->where('user_id', $user->id)
                    ->where('type', 'private')
                    ->delete();
            } else {
                OneToOneNote::query()->updateOrCreate(
                    ['one_to_one_request_id' => $oneToOneRequest->id, 'user_id' => $user->id, 'type' => 'private'],
                    ['note' => $note]
                );
            }
        }

        return back()->with('status', $statusMsg)->with('success',
            match ($statusMsg) {
                'one-to-one-completed'            => 'Incontro segnato come completato da entrambi.',
                'one-to-one-completion-confirmed' => 'Completamento confermato. Attendi conferma dell\'altro partecipante.',
                'one-to-one-rescheduled'          => 'Nuova proposta orario inviata. L\'altra parte riceverà una notifica.',
                default => 'Aggiornamento salvato.',
            }
        );
    }

    /**
     * Salva uno slot di disponibilità settimanale.
     * FIX #4: slot di esattamente 1 ora — ends_at calcolato automaticamente.
     */
    public function storeAvailability(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'weekday'      => ['required', 'integer', 'between:1,7'],
            'starts_at'    => ['required', 'date_format:H:i'],
            'meeting_mode' => ['required', Rule::in(['online', 'in_person'])],
            'location'     => ['nullable', 'string', 'max:255'],
        ]);

        // FIX #4: lo slot dura sempre esattamente 1 ora
        $endsAt = Carbon::createFromFormat('H:i', $data['starts_at'])->addHour()->format('H:i');

        AvailabilitySlot::query()->create([
            'user_id'      => $request->user()->id,
            'weekday'      => $data['weekday'],
            'starts_at'    => $data['starts_at'],
            'ends_at'      => $endsAt,
            'timezone'     => config('app.timezone', 'Europe/Rome'),
            'meeting_mode' => $data['meeting_mode'],
            'location'     => $data['location'] ?? null,
            'is_active'    => true,
        ]);

        return back()->with('status', 'availability-created')->with('success', 'Disponibilità aggiunta (slot di 1 ora).');
    }

    public function destroyAvailability(Request $request, AvailabilitySlot $availabilitySlot): RedirectResponse
    {
        $this->authorize('deleteSlot', $availabilitySlot);
        $availabilitySlot->delete();
        return back()->with('status', 'availability-deleted')->with('success', 'Slot rimosso.');
    }
}
