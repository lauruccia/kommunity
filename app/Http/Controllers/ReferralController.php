<?php

namespace App\Http\Controllers;

use App\Enums\OneToOneStatus;
use App\Enums\ReferralStatus;
use App\Models\OneToOneRequest;
use App\Models\Referral;
use App\Models\User;
use App\Notifications\ReferralClientConfirmedNotification;
use App\Notifications\ReferralClientReferredNotification;
use App\Notifications\ReferralConfirmedNotification;
use App\Notifications\ReferralReceivedNotification;
use App\Notifications\ReferralValueDeclaredNotification;
use App\Services\ReferralScoreService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReferralController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, ReferralScoreService $scores): View
    {
        $user    = $request->user();
        $isAdmin = $user->hasAnyRole(['super-admin', 'admin-community']);

        $filters = $request->validate([
            'search'   => ['nullable', 'string', 'max:255'],
            'status'   => ['nullable', Rule::in(array_column(ReferralStatus::cases(), 'value'))],
            'priority' => ['nullable', Rule::in(['1', '2', '3', '4', '5'])],
            'tab'      => ['nullable', 'string', 'in:ricevute,inviate,segnalato,archivio,classifica,moderazione'],
        ]);

        $eligibleMemberIds = $this->eligibleRecipientIds($user->id);

        $sentQuery = Referral::query()
            ->with(['recipient.memberProfile', 'client'])
            ->where('sender_id', $user->id);

        $receivedQuery = Referral::query()
            ->with(['sender.memberProfile', 'client'])
            ->where('recipient_id', $user->id);

        // Referenze in cui SONO il cliente segnalato.
        $clientQuery = Referral::query()
            ->with(['sender.memberProfile', 'recipient.memberProfile'])
            ->where('client_user_id', $user->id);

        foreach ([$sentQuery, $receivedQuery, $clientQuery] as $query) {
            if (! empty($filters['search'])) {
                $query->where(function ($q) use ($filters): void {
                    $q->where('title', 'like', '%'.$filters['search'].'%')
                      ->orWhere('description', 'like', '%'.$filters['search'].'%')
                      ->orWhere('company_name', 'like', '%'.$filters['search'].'%')
                      ->orWhere('contact_name', 'like', '%'.$filters['search'].'%');
                });
            }
            if (! empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (! empty($filters['priority'])) {
                $query->where('priority', $filters['priority']);
            }
        }

        // Per il pannello admin: tutte le referenze (paginato), con priorità a quelle da validare.
        $adminReferrals = null;
        $pendingValidation = 0;
        if ($isAdmin) {
            $adminQuery = Referral::query()
                ->with(['sender', 'recipient'])
                ->orderByRaw("CASE WHEN status = '".ReferralStatus::Completed->value."' THEN 0 ELSE 1 END")
                ->latest();
            if (! empty($filters['search'])) {
                $adminQuery->where(function ($q) use ($filters): void {
                    $q->where('title', 'like', '%'.$filters['search'].'%')
                      ->orWhere('description', 'like', '%'.$filters['search'].'%');
                });
            }
            $adminReferrals = $adminQuery->paginate(20, ['*'], 'admin');
            $pendingValidation = Referral::query()->where('status', ReferralStatus::Completed->value)->count();
        }

        return view('referrals.index', [
            'planetMembers' => User::query()
                ->whereIn('id', $this->planetMemberIds($user))
                ->whereHas('memberProfile', fn ($q) => $q->where('is_active', true))
                ->orderBy('name')
                ->get(['id', 'name']),
            'sentReferrals' => $sentQuery->latest()->paginate(20, ['*'], 'inviate')->withQueryString(),
            'receivedReferrals' => $receivedQuery->latest()->paginate(20, ['*'], 'ricevute')->withQueryString(),
            'clientReferrals' => $clientQuery->latest()->paginate(20, ['*'], 'segnalato')->withQueryString(),
            'adminReferrals' => $adminReferrals,
            'pendingValidation' => $pendingValidation,
            'statusOptions' => ReferralStatus::options(),
            'filters' => $filters,
            'eligibleMemberIds' => $eligibleMemberIds,
            'isAdmin' => $isAdmin,
            'activeTab' => $filters['tab'] ?? 'ricevute',
            'leaderboard' => $scores->leaderboard(20),
            'myScore'     => $scores->summaryFor($user->id),
            'summary' => [
                'sent'     => Referral::query()->where('sender_id', $user->id)->count(),
                'received' => Referral::query()->where('recipient_id', $user->id)->count(),
                'open'     => Referral::query()
                    ->where(fn ($q) => $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id))
                    ->whereIn('status', [
                        ReferralStatus::Sent->value, ReferralStatus::InProgress->value,
                        ReferralStatus::InCharge->value, ReferralStatus::Contacted->value, ReferralStatus::Negotiating->value,
                    ])
                    ->count(),
                'won' => Referral::query()
                    ->where(fn ($q) => $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id))
                    ->confirmed()
                    ->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user          = $request->user();
        $userId        = $user->id;
        $planetMembers = $this->planetMemberIds($user);

        $data = $request->validate([
            'recipient_id'   => ['required', 'exists:users,id', 'different:'.$userId],
            'client_user_id' => ['required', 'exists:users,id', 'different:'.$userId, 'different:recipient_id'],
            'description'    => ['required', 'string', 'max:5000'],
            'notes'          => ['nullable', 'string', 'max:3000'],
        ]);

        // Professionista e cliente devono essere membri dello stesso Pianeta.
        abort_unless(
            $planetMembers->contains((int) $data['recipient_id']) && $planetMembers->contains((int) $data['client_user_id']),
            403
        );

        $client = User::find($data['client_user_id']);

        $referral = Referral::query()->create([
            ...$data,
            'title'     => __('referrals.auto_title', ['client' => $client?->name ?? '—']),
            'priority'  => '3',
            'sender_id' => $userId,
            'status'    => ReferralStatus::Sent,
            'is_public' => true,
        ]);

        $referral->load('sender');

        // Avviso ENTRAMBI: il professionista e il cliente segnalato.
        $recipient = User::find($data['recipient_id']);
        $recipient?->notify(new ReferralReceivedNotification($referral));

        $client = User::find($data['client_user_id']);
        $client?->notify(new ReferralClientReferredNotification($referral));

        return redirect()
            ->route('referrals.index', ['tab' => 'inviate'])
            ->with([
                'status'       => 'referral-created',
                'suggest_name' => $recipient?->name ?? 'il destinatario',
                'suggest_id'   => $data['recipient_id'],
                'suggest_title'=> $data['title'],
            ]);
    }

    /**
     * Aggiornamento "leggero" di stato/note/esito (presa in carico, annullamento).
     * NON gestisce la dichiarazione del valore né la validazione (metodi dedicati).
     */
    public function updateStatus(Request $request, Referral $referral): RedirectResponse
    {
        $this->authorize('updateStatus', $referral);

        $isAdmin = $request->user()->hasAnyRole(['super-admin', 'admin-community']);

        // Stati ammessi qui: solo transizioni operative. Confirmed/Rejected passano da validateValue().
        $allowed = [
            ReferralStatus::Sent->value,
            ReferralStatus::InProgress->value,
            ReferralStatus::Cancelled->value,
        ];
        if ($isAdmin) {
            $allowed = array_column(ReferralStatus::currentCases(), 'value');
        }

        $data = $request->validate([
            'status'  => ['required', Rule::in($allowed)],
            'notes'   => ['nullable', 'string', 'max:3000'],
            'outcome' => ['nullable', 'string', 'max:3000'],
        ]);

        $referral->update($data);

        return back()->with('status', 'referral-updated');
    }

    /**
     * Il professionista (destinatario) prende in carico la referenza.
     */
    public function acknowledge(Request $request, Referral $referral): RedirectResponse
    {
        $this->authorize('acknowledge', $referral);

        if (in_array($referral->status, [ReferralStatus::Sent], true)) {
            $referral->update([
                'status'          => ReferralStatus::InProgress,
                'acknowledged_at' => now(),
            ]);
        }

        return back()->with('status', 'referral-acknowledged');
    }

    /**
     * Il professionista dichiara il valore della consulenza realizzata.
     * "Grazie a <segnalatore> ho realizzato una consulenza di X €."
     * → stato Completed, in attesa di validazione admin.
     */
    public function declareValue(Request $request, Referral $referral): RedirectResponse
    {
        $this->authorize('declareValue', $referral);

        $data = $request->validate([
            'declared_value' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'outcome'        => ['nullable', 'string', 'max:3000'],
        ]);

        $referral->update([
            'declared_value' => $data['declared_value'],
            'declared_at'    => now(),
            'outcome'        => $data['outcome'] ?? $referral->outcome,
            'status'         => ReferralStatus::Completed,
        ]);

        // Prossimo passo: il cliente deve confermare il servizio. Avviso cliente + segnalatore.
        $referral->loadMissing('sender', 'recipient', 'client');
        $referral->client?->notify(new ReferralValueDeclaredNotification($referral));
        $referral->sender?->notify(new ReferralValueDeclaredNotification($referral));

        return back()->with('status', 'referral-declared');
    }

    /**
     * Il cliente segnalato conferma di aver ricevuto il servizio.
     * → stato ClientConfirmed, in attesa di validazione admin.
     */
    public function clientConfirm(Request $request, Referral $referral): RedirectResponse
    {
        $this->authorize('clientConfirm', $referral);

        // Si conferma solo dopo che il professionista ha dichiarato il valore.
        if ($referral->status === ReferralStatus::Completed) {
            $referral->update([
                'client_confirmed_at' => now(),
                'status'              => ReferralStatus::ClientConfirmed,
            ]);

            $referral->loadMissing('sender', 'client');
            $referral->sender?->notify(new ReferralClientConfirmedNotification($referral));
            $this->admins()->each->notify(new ReferralClientConfirmedNotification($referral));
        }

        return back()->with('status', 'referral-client-confirmed');
    }

    /**
     * L'admin valida (o rifiuta) il valore dichiarato.
     * Solo dopo l'approvazione il valore concorre alla classifica e ai premi.
     */
    public function validateValue(Request $request, Referral $referral): RedirectResponse
    {
        $this->authorize('validateValue', $referral);

        $data = $request->validate([
            'decision'       => ['required', Rule::in(['approve', 'reject'])],
            'approved_value' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
        ]);

        if ($data['decision'] === 'approve') {
            $referral->update([
                'approved_value' => $data['approved_value'] ?? $referral->declared_value,
                'approved_at'    => now(),
                'approved_by'    => $request->user()->id,
                'status'         => ReferralStatus::Confirmed,
            ]);

            $referral->loadMissing('sender');
            $referral->sender?->notify(new ReferralConfirmedNotification($referral));

            return back()->with('status', 'referral-confirmed');
        }

        $referral->update([
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
            'status'      => ReferralStatus::Rejected,
        ]);

        return back()->with('status', 'referral-rejected');
    }

    public function togglePublic(Request $request, Referral $referral): RedirectResponse
    {
        $this->authorize('togglePublic', $referral);

        $referral->update(['is_public' => ! $referral->is_public]);

        return back()->with('status', $referral->is_public ? 'referral-public' : 'referral-private');
    }

    public function destroy(Request $request, Referral $referral): RedirectResponse
    {
        $this->authorize('destroy', $referral);

        $referral->delete();

        return back()->with('status', 'referral-deleted');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function admins()
    {
        return User::query()->whereHas('roles', fn ($q) => $q->whereIn('name', ['super-admin', 'admin-community']))->get();
    }

    /**
     * ID dei membri che condividono almeno un Pianeta attivo con l'utente
     * (esclude se stesso). Sono i candidati selezionabili come professionista o cliente.
     */
    private function planetMemberIds(User $user): Collection
    {
        $planetIds = $user->planets()->pluck('chapters.id');

        if ($planetIds->isEmpty()) {
            return collect();
        }

        return DB::table('chapter_members')
            ->whereIn('chapter_id', $planetIds)
            ->where('status', 'active')
            ->where('user_id', '!=', $user->id)
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    private function eligibleRecipientIds(int $userId): array
    {
        return OneToOneRequest::query()
            ->where('status', OneToOneStatus::Completed->value)
            ->whereNotNull('requester_completed_at')
            ->whereNotNull('recipient_completed_at')
            ->where(function ($query) use ($userId): void {
                $query->where('requester_id', $userId)->orWhere('recipient_id', $userId);
            })
            ->get(['requester_id', 'recipient_id'])
            ->flatMap(function (OneToOneRequest $o) use ($userId) {
                return [$o->requester_id === $userId ? $o->recipient_id : $o->requester_id];
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
