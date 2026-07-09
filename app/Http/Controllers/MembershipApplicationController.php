<?php

namespace App\Http\Controllers;

use App\Mail\MembershipApplicationReceivedMail;
use App\Models\Chapter;
use App\Models\MemberOnepage;
use App\Models\MembershipApplication;
use App\Models\User;
use App\Notifications\NewMembershipApplicationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Candidature di ammissione a Kommunity (rotta pubblica, nessuna auth).
 *
 * Due punti di ingresso, stessa rotta POST:
 *   - card di un membro  → campo hidden card_slug: il pianeta proposto è
 *     quello attivo del proprietario della card, che risulta "presentatore"
 *   - homepage           → nessun card_slug: il pianeta proposto è Kosmos
 *
 * L'ammissione resta SEMPRE soggetta ad approvazione admin (Filament:
 * /admin/membership-applications).
 */
class MembershipApplicationController extends Controller
{
    /** Slug del Pianeta di default per le candidature dalla homepage. */
    private const DEFAULT_PLANET_SLUG = 'kosmos';

    public function store(Request $request): RedirectResponse
    {
        // ── Honeypot anti-bot: campo invisibile che gli umani non compilano ──
        if (filled($request->input('company_website'))) {
            return back();
        }

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255', 'regex:/^\S+\s+\S+.*$/'],
            'email'          => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'phone'          => ['required', 'string', 'max:30', 'regex:/^[0-9+().\s-]{6,30}$/'],
            'applicant_type' => ['required', 'in:privato,azienda'],
            'vat_number'     => ['nullable', 'string', 'max:20', 'required_if:applicant_type,azienda'],
            'profession'     => ['required', 'string', 'max:255'],
            'referrer_name'  => ['nullable', 'string', 'max:255'],
            'card_slug'      => ['nullable', 'string', 'max:255'],
            'locale'         => ['nullable', 'string', 'max:5'],
        ], [
            'name.required'           => __('application.v_name_required'),
            'name.regex'              => __('application.v_name_full'),
            'email.required'          => __('application.v_email_required'),
            'email.email'             => __('application.v_email_valid'),
            'phone.required'          => __('application.v_phone_required'),
            'phone.regex'             => __('application.v_phone_valid'),
            'applicant_type.required' => __('application.v_type_required'),
            'applicant_type.in'       => __('application.v_type_required'),
            'vat_number.required_if'  => __('application.v_vat_required'),
            'profession.required'     => __('application.v_profession_required'),
        ]);

        $email = strtolower(trim($validated['email']));

        // ── Già membro? ──────────────────────────────────────────────────────
        if (User::query()->where('email', $email)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['email' => __('application.error_already_member')]);
        }

        // ── Candidatura già in valutazione? ──────────────────────────────────
        $alreadyPending = MembershipApplication::query()
            ->pending()
            ->where('email', $email)
            ->exists();

        if ($alreadyPending) {
            return back()
                ->withInput()
                ->withErrors(['email' => __('application.error_already_pending')]);
        }

        // ── Origine: card membro o homepage ──────────────────────────────────
        $source    = MembershipApplication::SOURCE_HOME;
        $presenter = null;
        $chapter   = null;

        if (filled($validated['card_slug'] ?? null)) {
            $onepage = MemberOnepage::query()
                ->with('user')
                ->where('slug', $validated['card_slug'])
                ->where('is_active', true)
                ->first();

            if ($onepage?->user) {
                $source    = MembershipApplication::SOURCE_CARD;
                $presenter = $onepage->user;
                $chapter   = $presenter->activePlanet();
            }
        }

        // Fallback (homepage o presentatore senza pianeta): Pianeta Kosmos
        if (! $chapter) {
            $chapter = Chapter::query()
                    ->where('slug', self::DEFAULT_PLANET_SLUG)
                    ->where('is_active', true)
                    ->first()
                ?? Chapter::query()
                    ->where('name', 'like', '%kosmos%')
                    ->where('is_active', true)
                    ->first();
        }

        // Lingua del candidato (per le email): dal form (card multilingua)
        // oppure dal locale corrente dell'app (homepage)
        $locale = $validated['locale'] ?? app()->getLocale();
        if (! in_array($locale, ['it', 'en', 'fr', 'es', 'de', 'ro'], true)) {
            $locale = 'it';
        }

        $application = MembershipApplication::create([
            'source'            => $source,
            'presenter_user_id' => $presenter?->id,
            'chapter_id'        => $chapter?->id,
            'name'              => trim($validated['name']),
            'email'             => $email,
            'phone'             => trim($validated['phone']),
            'applicant_type'    => $validated['applicant_type'],
            'vat_number'        => filled($validated['vat_number'] ?? null) ? trim($validated['vat_number']) : null,
            'profession'        => trim($validated['profession']),
            'referrer_name'     => filled($validated['referrer_name'] ?? null) ? trim($validated['referrer_name']) : null,
            'locale'            => $locale,
            'status'            => MembershipApplication::STATUS_PENDING,
        ]);

        // ── Email di conferma al candidato (mai bloccante) ───────────────────
        try {
            Mail::to($application->email)
                ->send((new MembershipApplicationReceivedMail($application))
                    ->locale($application->mailLocale()));
        } catch (\Throwable $e) {
            Log::warning('Email conferma candidatura non inviata: ' . $e->getMessage(), [
                'application_id' => $application->id,
            ]);
        }

        // ── Notifica gli admin: email + database + push ──────────────────────
        try {
            $admins = User::query()
                ->whereNotNull('email')
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['super-admin', 'admin-community']))
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new NewMembershipApplicationNotification($application));
            }
        } catch (\Throwable $e) {
            Log::warning('Notifica admin candidatura non inviata: ' . $e->getMessage(), [
                'application_id' => $application->id,
            ]);
        }

        return back()->with('membership_applied', true);
    }
}
