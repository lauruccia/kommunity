<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\EventRegistration;
use App\Models\MemberOnepage;
use App\Models\OneToOneReference;
use App\Models\OneToOneRequest;
use App\Models\ProfileVideoAccessRequest;
use App\Models\Referral;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class MemberOnepageController extends Controller
{
    public function reviews(Request $request, string $slug): View
    {
        $onepage = MemberOnepage::query()
            ->with(['user.memberProfile'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $memberUserId = $onepage->user->id;
        $sort         = $request->get('sort', 'recenti');

        $query = OneToOneReference::query()
            ->with('author')
            ->where('recipient_id', $memberUserId)
            ->where(function ($q): void {
                $q->whereNotNull('content')
                  ->orWhereNotNull('rating');
            });

        match ($sort) {
            'migliori' => $query->orderBy('rating', 'desc'),
            'peggiori' => $query->orderBy('rating', 'asc'),
            default    => $query->latest(),
        };

        $reviews    = $query->paginate(12)->withQueryString();
        $totalCount = OneToOneReference::query()
            ->where('recipient_id', $memberUserId)
            ->where(function ($q): void {
                $q->whereNotNull('content')->orWhereNotNull('rating');
            })
            ->count();

        $avgRating = (function () use ($memberUserId): ?float {
            $ratings = OneToOneReference::query()
                ->where('recipient_id', $memberUserId)
                ->whereNotNull('rating')
                ->where('rating', '>', 0)
                ->pluck('rating');
            return $ratings->isNotEmpty() ? round($ratings->avg(), 1) : null;
        })();

        return view('members.reviews', [
            'onepage'    => $onepage,
            'profile'    => $onepage->user->memberProfile,
            'user'       => $onepage->user,
            'reviews'    => $reviews,
            'totalCount' => $totalCount,
            'avgRating'  => $avgRating,
            'sort'       => $sort,
        ]);
    }

    public function referrals(Request $request, string $slug): View
    {
        $onepage = MemberOnepage::query()
            ->with(['user.memberProfile'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $memberUserId = $onepage->user->id;
        $sort         = $request->get('sort', 'recenti');

        $query = Referral::query()
            ->with('sender')
            ->where('recipient_id', $memberUserId)
            ->where('is_public', true);

        match ($sort) {
            'migliori' => $query->orderByRaw('CAST(priority AS UNSIGNED) DESC'),
            'peggiori' => $query->orderByRaw('CAST(priority AS UNSIGNED) ASC'),
            default    => $query->latest(),
        };

        $referrals  = $query->paginate(12)->withQueryString();
        $totalCount = Referral::query()->where('recipient_id', $memberUserId)->where('is_public', true)->count();

        $avgPriority = (function () use ($memberUserId): ?float {
            $rows = Referral::query()
                ->where('recipient_id', $memberUserId)
                ->where('is_public', true)
                ->whereNotNull('priority')
                ->pluck('priority');
            if ($rows->isEmpty()) {
                return null;
            }
            $numeric = $rows->map(fn ($p) => match (true) {
                in_array($p, ['1','2','3','4','5'], true) => (int) $p,
                $p === 'high' => 5,
                $p === 'low'  => 1,
                default       => 3,
            });

            return round($numeric->avg(), 1);
        })();

        return view('members.referrals', [
            'onepage'     => $onepage,
            'profile'     => $onepage->user->memberProfile,
            'user'        => $onepage->user,
            'referrals'   => $referrals,
            'totalCount'  => $totalCount,
            'avgPriority' => $avgPriority,
            'sort'        => $sort,
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $onepage = MemberOnepage::query()
            ->with([
                'user.memberProfile.categories',
                'user.memberProfile.category',
                'user.memberProfile.professions',
                'user.memberProfile.profession',
                'user.memberProfile.sector',
                'user.memberProfile.city',
                'user.memberProfile.region',
                'user.memberProfile.chapter',
                'user.memberProfile.companyInterestTypes',
                'user.memberGalleryImages',
            ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $currentUserId = $request->user()?->id;
        $memberUserId  = $onepage->user->id;
        $videoAccessRequest = null;

        if ($currentUserId && $currentUserId !== $memberUserId && Schema::hasTable('profile_video_access_requests')) {
            $videoAccessRequest = ProfileVideoAccessRequest::query()
                ->between($currentUserId, $memberUserId)
                ->latest()
                ->first();
        }

        // Recensioni ricevute da questo membro (con testo o voto)
        $reviews = Schema::hasTable('one_to_one_references')
            ? OneToOneReference::query()
                ->with('author')
                ->where('recipient_id', $memberUserId)
                ->where(function ($q): void {
                    $q->whereNotNull('content')
                      ->orWhereNotNull('rating');
                })
                ->latest()
                ->get()
            : collect();

        return view('members.show', [
            'onepage'         => $onepage,
            'profile'         => $onepage->user->memberProfile,
            'user'            => $onepage->user,
            'reviews'         => $reviews,
            'canViewIntroVideo' => $onepage->user->memberProfile?->canViewIntroVideo($request->user()) ?? false,
            'videoAccessRequest' => $videoAccessRequest,
            'currentTab'      => $request->string('tab')->toString() ?: 'profile',
            'communityThreads' => $onepage->user->forumThreads()
                ->with('category')
                ->latest()
                ->take(4)
                ->get(),
            'sharedOneToOnes' => $currentUserId ? OneToOneRequest::query()
                ->with(['requester', 'recipient'])
                ->where(function ($query) use ($currentUserId, $memberUserId): void {
                    $query
                        ->where('requester_id', $currentUserId)
                        ->where('recipient_id', $memberUserId);
                })
                ->orWhere(function ($query) use ($currentUserId, $memberUserId): void {
                    $query
                        ->where('requester_id', $memberUserId)
                        ->where('recipient_id', $currentUserId);
                })
                ->latest()
                ->take(6)
                ->get() : collect(),
            'sharedReferrals' => $currentUserId ? Referral::query()
                ->with(['sender', 'recipient'])
                ->where(function ($query) use ($currentUserId, $memberUserId): void {
                    $query
                        ->where('sender_id', $currentUserId)
                        ->where('recipient_id', $memberUserId);
                })
                ->orWhere(function ($query) use ($currentUserId, $memberUserId): void {
                    $query
                        ->where('sender_id', $memberUserId)
                        ->where('recipient_id', $currentUserId);
                })
                ->latest()
                ->take(6)
                ->get() : collect(),
            'publicEndorsements' => Referral::query()
                ->with('sender')
                ->where('recipient_id', $memberUserId)
                ->where('is_public', true)
                ->latest()
                ->take(6)
                ->get(),
            'receivedReferralsCount' => Referral::query()
                ->where('recipient_id', $memberUserId)
                ->where('is_public', true)
                ->count(),
            'wonReferralsCount' => Referral::query()
                ->where('recipient_id', $memberUserId)
                ->where('is_public', true)
                ->where('status', 'won')
                ->count(),
            // Conversazione privata tra i due (ultimo messaggio)
            'sharedConversation' => $currentUserId ? Conversation::query()
                ->whereHas('participants', fn ($q) => $q->where('users.id', $currentUserId))
                ->whereHas('participants', fn ($q) => $q->where('users.id', $memberUserId))
                ->with(['lastMessage.user'])
                ->latest('updated_at')
                ->first() : null,

            // Co-partecipazione eventi (eventi a cui entrambi sono iscritti)
            'sharedEvents' => $currentUserId ? \App\Models\Event::query()
                ->whereHas('registrations', fn ($q) => $q->where('user_id', $currentUserId))
                ->whereHas('registrations', fn ($q) => $q->where('user_id', $memberUserId))
                ->with(['registrations' => fn ($q) => $q->where('user_id', $currentUserId)])
                ->orderByDesc('starts_at')
                ->take(4)
                ->get() : collect(),

            'receivedReferralsAvgPriority' => (function () use ($memberUserId): ?float {
                $rows = Referral::query()
                    ->where('recipient_id', $memberUserId)
                    ->where('is_public', true)
                    ->whereNotNull('priority')
                    ->pluck('priority');
                if ($rows->isEmpty()) {
                    return null;
                }
                $numeric = $rows->map(fn ($p) => match (true) {
                    in_array($p, ['1','2','3','4','5'], true) => (int) $p,
                    $p === 'high'   => 5,
                    $p === 'low'    => 1,
                    default         => 3,
                });
                return round($numeric->avg(), 1);
            })(),
        ]);
    }
}
