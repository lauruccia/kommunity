<?php

namespace App\Http\Controllers;

use App\Enums\ContactMethod;
use App\Models\MemberProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    /**
     * Salva i campi di uno step senza marcare l'onboarding come completato.
     * Risponde in JSON per essere chiamato via fetch() da Alpine.js.
     */
    public function saveStep(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_name'             => ['nullable', 'string', 'max:255'],
            'short_bio'                => ['nullable', 'string', 'max:500'],
            'networking_goals'         => ['nullable', 'string', 'max:2000'],
            'services'                 => ['nullable', 'string', 'max:2000'],
            'website'                  => ['nullable', 'url', 'max:255'],
            'linkedin_url'             => ['nullable', 'url', 'max:255'],
            'phone'                    => ['nullable', 'string', 'max:30'],
            'preferred_contact_method' => ['nullable', Rule::in(array_column(ContactMethod::cases(), 'value'))],
        ]);

        // Filtra i campi null in modo da non sovrascrivere dati già esistenti
        $payload = array_filter($data, fn ($v) => $v !== null);

        if (! empty($payload)) {
            MemberProfile::query()->updateOrCreate(
                ['user_id' => $request->user()->id],
                $payload
            );
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Marca l'onboarding come completato e imposta lo stato a pending_approval.
     */
    public function complete(Request $request): RedirectResponse
    {
        $profile = MemberProfile::query()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['status' => 'pending_approval']
        );

        $profile->onboarding_completed = true;

        // Porta a pending_approval solo se il profilo è ancora in stato draft
        if (in_array($profile->status?->value, ['draft', null], true)) {
            $profile->status = 'pending_approval';
        }

        $profile->save();

        return redirect()->route('dashboard')->with('status', 'onboarding-completed');
    }
}
