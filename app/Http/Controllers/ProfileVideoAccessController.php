<?php

namespace App\Http\Controllers;

use App\Models\ProfileVideoAccessRequest;
use App\Models\User;
use App\Notifications\ProfileVideoAccessRequestedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ProfileVideoAccessController extends Controller
{
    public function store(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->id === $user->id, 403);

        if (! Schema::hasTable('profile_video_access_requests')) {
            return back()->with('status', 'video-access-unavailable');
        }

        if (! $request->user()->memberProfile?->hasVideo()) {
            return back()->with('status', 'video-access-own-video-required');
        }

        $accessRequest = ProfileVideoAccessRequest::query()->updateOrCreate(
            [
                'requester_id' => $request->user()->id,
                'recipient_id' => $user->id,
            ],
            [
                'status' => 'pending',
                'requested_at' => now(),
                'responded_at' => null,
                'revoked_at' => null,
            ]
        );

        try {
            $user->notify(new ProfileVideoAccessRequestedNotification($accessRequest, $request->user()));
        } catch (\Throwable) {
            // La richiesta resta valida anche se una notifica esterna fallisce.
        }

        return back()->with('status', 'video-access-requested');
    }

    public function respond(Request $request, int $profileVideoAccessRequest): RedirectResponse
    {
        if (! Schema::hasTable('profile_video_access_requests')) {
            return back()->with('status', 'video-access-unavailable');
        }

        $profileVideoAccessRequest = ProfileVideoAccessRequest::query()->findOrFail($profileVideoAccessRequest);

        abort_unless($profileVideoAccessRequest->recipient_id === $request->user()->id, 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'declined'])],
        ]);

        $profileVideoAccessRequest->update([
            'status' => $validated['status'],
            'responded_at' => now(),
            'revoked_at' => null,
        ]);

        return back()->with('status', $validated['status'] === 'accepted'
            ? 'video-access-accepted'
            : 'video-access-declined');
    }

    public function revoke(Request $request, int $profileVideoAccessRequest): RedirectResponse
    {
        if (! Schema::hasTable('profile_video_access_requests')) {
            return back()->with('status', 'video-access-unavailable');
        }

        $profileVideoAccessRequest = ProfileVideoAccessRequest::query()->findOrFail($profileVideoAccessRequest);

        abort_unless(
            $profileVideoAccessRequest->requester_id === $request->user()->id
            || $profileVideoAccessRequest->recipient_id === $request->user()->id,
            403
        );

        $profileVideoAccessRequest->update([
            'status' => 'revoked',
            'revoked_at' => now(),
        ]);

        return back()->with('status', 'video-access-revoked');
    }
}
