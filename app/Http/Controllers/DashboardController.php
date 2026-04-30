<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Event;
use App\Models\ForumThread;
use App\Models\OneToOneRequest;
use App\Models\Referral;
use App\Services\Features;
use App\Services\MemberAnalyticsService;
use App\Services\ProfileCompletionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load(['memberProfile.city', 'memberProfile.category', 'memberOnepage', 'memberProfile.professions']);

        $conversationIds = Conversation::query()
            ->whereHas('participants', fn ($query) => $query->where('users.id', $user->id))
            ->pluck('id');

        $showOnboarding    = ! optional($user->memberProfile)->onboarding_completed;
        $profileCompletion = (new ProfileCompletionService())->calculate($user);

        // ── Feature: dashboard analytics personale (gated) ───────────────────
        $analytics = Features::enabled('analytics_personal')
            ? (new MemberAnalyticsService())->calculate($user)
            : null;

        return view('dashboard', [
            'user'              => $user,
            'showOnboarding'    => $showOnboarding,
            'profileCompletion' => $profileCompletion,
            'analytics'         => $analytics,
            'upcomingEvents' => Event::query()
                ->where('is_published', true)
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(3)
                ->get(),
            'receivedOneToOnes' => OneToOneRequest::query()
                ->with('requester.memberProfile.city')
                ->where('recipient_id', $user->id)
                ->latest()
                ->limit(4)
                ->get(),
            'sentReferrals' => Referral::query()
                ->with('recipient.memberProfile')
                ->where('sender_id', $user->id)
                ->latest()
                ->limit(4)
                ->get(),
            'latestThreads' => ForumThread::query()
                ->with(['category', 'user'])
                ->latest()
                ->limit(4)
                ->get(),
            'recentMessages' => \App\Models\Message::query()
                ->with('user')
                ->whereIn('conversation_id', $conversationIds)
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
