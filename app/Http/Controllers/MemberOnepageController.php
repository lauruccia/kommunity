<?php

namespace App\Http\Controllers;

use App\Models\MemberOnepage;
use App\Models\OneToOneRequest;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class MemberOnepageController extends Controller
{
    public function show(Request $request, string $slug): View
    {
        $onepage = MemberOnepage::query()
            ->with([
                'user.memberProfile.categories',
                'user.memberProfile.professions',
                'user.memberProfile.profession',
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
        $memberUserId = $onepage->user->id;

        return view('members.show', [
            'onepage' => $onepage,
            'profile' => $onepage->user->memberProfile,
            'user' => $onepage->user,
            'currentTab' => $request->string('tab')->toString() ?: 'profile',
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
        ]);
    }
}
