<?php

namespace App\Http\Controllers;

use App\Enums\OneToOneStatus;
use App\Enums\ReferralStatus;
use App\Models\OneToOneRequest;
use App\Models\Referral;
use App\Models\User;
use App\Notifications\ReferralReceivedNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReferralController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View
    {
        $user    = $request->user();
        $isAdmin = $user->hasAnyRole(['super-admin', 'admin-community']);

        $filters = $request->validate([
            'search'   => ['nullable', 'string', 'max:255'],
            'status'   => ['nullable', Rule::in(array_column(ReferralStatus::cases(), 'value'))],
            'priority' => ['nullable', Rule::in(['1', '2', '3', '4', '5'])],
            'tab'      => ['nullable', 'string', 'in:ricevute,inviate,archivio,moderazione'],
        ]);

        $eligibleMemberIds = $this->eligibleRecipientIds($user->id);

        $sentQuery = Referral::query()
            ->with(['recipient.memberProfile'])
            ->where('sender_id', $user->id);

        $receivedQuery = Referral::query()
            ->with(['sender.memberProfile'])
            ->where('recipient_id', $user->id);

        foreach ([$sentQuery, $receivedQuery] as $query) {
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

        // Per il pannello admin: tutte le referenze (paginato)
        $adminReferrals = null;
        if ($isAdmin) {
            $adminQuery = Referral::query()->with(['sender', 'recipient'])->latest();
            if (! empty($filters['search'])) {
                $adminQuery->where(function ($q) use ($filters): void {
                    $q->where('title', 'like', '%'.$filters['search'].'%')
                      ->orWhere('description', 'like', '%'.$filters['search'].'%');
                });
            }
            $adminReferrals = $adminQuery->paginate(20, ['*'], 'admin');
        }

        return view('referrals.index', [
            'members' => User::query()
                ->with('memberProfile')
                ->whereKeyNot($user->id)
                ->whereIn('id', $eligibleMemberIds)
                ->whereHas('memberProfile', fn ($q) => $q->where('is_active', true))
                ->orderBy('name')
                ->get(),
            'sentReferrals' => $sentQuery->latest()->paginate(20, ['*'], 'inviate')->withQueryString(),
            'receivedReferrals' => $receivedQuery->latest()->paginate(20, ['*'], 'ricevute')->withQueryString(),
            'adminReferrals' => $adminReferrals,
            'statusOptions' => ReferralStatus::options(),
            'filters' => $filters,
            'eligibleMemberIds' => $eligibleMemberIds,
            'isAdmin' => $isAdmin,
            'activeTab' => $filters['tab'] ?? 'ricevute',
            'summary' => [
                'sent'     => Referral::query()->where('sender_id', $user->id)->count(),
                'received' => Referral::query()->where('recipient_id', $user->id)->count(),
                'open'     => Referral::query()
                    ->where(fn ($q) => $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id))
                    ->whereNotIn('status', [ReferralStatus::Won->value, ReferralStatus::Lost->value, ReferralStatus::Archived->value])
                    ->count(),
                'value' => (float) Referral::query()->where('sender_id', $user->id)->sum('estimated_value'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $eligibleMemberIds = $this->eligibleRecipientIds($request->user()->id);
        $data = $request->validate([
            'recipient_id' => ['required', 'exists:users,id', 'different:'.$request->user()->id],
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['required', 'string', 'max:5000'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'priority' => ['nullable', Rule::in(['1', '2', '3', '4', '5'])],
            'notes'    => ['nullable', 'string', 'max:3000'],
        ]);

        abort_unless(in_array((int) $data['recipient_id'], $eligibleMemberIds, true), 403);

        $referral = Referral::query()->create([
            ...$data,
            'priority'  => $data['priority'] ?? '3',
            'sender_id' => $request->user()->id,
            'status'    => ReferralStatus::Sent,
        ]);

        $recipient = User::find($data['recipient_id']);
        $referral->load('sender');
        $recipient?->notify(new ReferralReceivedNotification($referral));

        return redirect()
            ->route('referrals.index', ['tab' => 'inviate'])
            ->with([
                'status'       => 'referral-created',
                'suggest_name' => $recipient?->name ?? 'il destinatario',
                'suggest_id'   => $data['recipient_id'],
                'suggest_title'=> $data['title'],
            ]);
    }

    public function updateStatus(Request $request, Referral $referral): RedirectResponse
    {
        $this->authorize('updateStatus', $referral);

        $data = $request->validate([
            'status'  => ['required', Rule::in(array_column(ReferralStatus::cases(), 'value'))],
            'notes'   => ['nullable', 'string', 'max:3000'],
            'outcome' => ['nullable', 'string', 'max:3000'],
        ]);

        $referral->update($data);

        return back()->with('status', 'referral-updated');
    }

    public function acknowledge(Request $request, Referral $referral): RedirectResponse
    {
        $this->authorize('acknowledge', $referral);

        if ($referral->status === ReferralStatus::Sent) {
            $referral->update([
                'status'          => ReferralStatus::InCharge,
                'acknowledged_at' => now(),
            ]);
        }

        return back()->with('status', 'referral-acknowledged');
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
