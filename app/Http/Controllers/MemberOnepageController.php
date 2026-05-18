<?php

namespace App\Http\Controllers;

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

        $currentUserId = $request->user()->id;
        $memberUserId  = $onepage->user->id;
        $videoAccessRequest = null;

        if ($currentUserId !== $memberUserId && Schema::hasTable('profile_video_access_requests')) {
            $videoAccessRequest = ProfileVideoAccessRequest::query()
                ->between($currentUserId, $memberUserId)
                ->latest()
                ->first();
        }

        // Recensioni ricevute da questo membro (con testo o voto)
        $reviews = OneToOneReference::query()
            ->with('author')
            ->where('recipient_id', $memberUserId)
            ->where(function ($q): void {
                $q->whereNotNull('content')
                  ->orWhereNotNull('rating');
            })
            ->latest()
            ->get();

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
            'sharedOneToOnes' => OneToOneRequest::query()
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
                ->get(),
            'sharedReferrals' => Referral::query()
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
                ->get(),
            'receivedReferralsCount' => Referral::query()
                ->where('recipient_id', $memberUserId)
                ->count(),
        ]);
    }
}
