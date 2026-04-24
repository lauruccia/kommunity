<?php

namespace App\Http\Controllers;

use App\Enums\ReferralStatus;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReferralController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(array_column(ReferralStatus::cases(), 'value'))],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high'])],
        ]);

        $sentQuery = Referral::query()
            ->with(['recipient.memberProfile'])
            ->where('sender_id', $user->id);

        $receivedQuery = Referral::query()
            ->with(['sender.memberProfile'])
            ->where('recipient_id', $user->id);

        foreach ([$sentQuery, $receivedQuery] as $query) {
            if (! empty($filters['search'])) {
                $query->where(function ($nestedQuery) use ($filters): void {
                    $nestedQuery
                        ->where('title', 'like', '%'.$filters['search'].'%')
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

        return view('referrals.index', [
            'members' => User::query()
                ->with('memberProfile')
                ->whereKeyNot($user->id)
                ->whereHas('memberProfile', fn ($query) => $query->where('is_active', true))
                ->orderBy('name')
                ->get(),
            'sentReferrals' => $sentQuery
                ->latest()
                ->paginate(8, ['*'], 'inviate')
                ->withQueryString(),
            'receivedReferrals' => $receivedQuery
                ->latest()
                ->paginate(8, ['*'], 'ricevute')
                ->withQueryString(),
            'statusOptions' => ReferralStatus::options(),
            'filters' => $filters,
            'summary' => [
                'sent' => Referral::query()->where('sender_id', $user->id)->count(),
                'received' => Referral::query()->where('recipient_id', $user->id)->count(),
                'open' => Referral::query()
                    ->where(fn ($query) => $query->where('sender_id', $user->id)->orWhere('recipient_id', $user->id))
                    ->whereNotIn('status', [ReferralStatus::Won->value, ReferralStatus::Lost->value, ReferralStatus::Archived->value])
                    ->count(),
                'value' => (float) Referral::query()
                    ->where('sender_id', $user->id)
                    ->sum('estimated_value'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'recipient_id' => ['required', 'exists:users,id', 'different:' . $request->user()->id],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);

        Referral::query()->create([
            ...$data,
            'sender_id' => $request->user()->id,
            'status' => ReferralStatus::Sent,
        ]);

        return back()->with('status', 'referral-created');
    }

    public function updateStatus(Request $request, Referral $referral): RedirectResponse
    {
        abort_unless(
            in_array($request->user()->id, [$referral->sender_id, $referral->recipient_id], true),
            403
        );

        $data = $request->validate([
            'status' => ['required', Rule::in(array_column(ReferralStatus::cases(), 'value'))],
            'notes' => ['nullable', 'string', 'max:3000'],
            'outcome' => ['nullable', 'string', 'max:3000'],
        ]);

        $referral->update($data);

        return back()->with('status', 'referral-updated');
    }
}
