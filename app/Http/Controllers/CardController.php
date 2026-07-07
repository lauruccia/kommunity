<?php

namespace App\Http\Controllers;

use App\Models\MemberOnepage;
use App\Models\MemberProfile;
use App\Services\ProfileCompletionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class CardController extends Controller
{
    /**
     * Pagina pubblica "biglietto da visita digitale".
     * Standalone: nessun layout app, nessuna navigazione.
     */
    public function show(Request $request, string $slug): View
    {
        $onepage = MemberOnepage::query()
            ->with(['user'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $user = $onepage->user;

        // ── Risoluzione robusta del profilo ──────────────────────────────────
        // Non ci affidiamo alla relazione hasOne non filtrata (User::memberProfile):
        // se per qualsiasi motivo l'utente avesse PIÙ righe in member_profiles
        // (es. account/profili duplicati storici, omonimie con doppia registra-
        // zione), selezioniamo sempre quella ATTIVA e più recente. Così la card
        // non rischia di agganciare per errore una riga vuota.
        $profile = $this->resolveProfile($user->id, ['professions', 'profession', 'city', 'chapter']);

        // ── La card ha dati reali da mostrare? ───────────────────────────────
        // Evita la "card vuota": se il profilo manca del tutto o non contiene
        // alcun dato identificativo/di contatto, la view mostra un fallback
        // pulito ("profilo in allestimento") invece di un biglietto privo di
        // informazioni utili.
        $hasProfileData = $profile !== null && (
            filled($profile->avatar)
            || filled($profile->phone)
            || filled($profile->website)
            || filled($profile->company_name)
            || $profile->city !== null
            || $profile->professions->isNotEmpty()
            || $profile->profession !== null
            || filled($profile->linkedin_url)
            || filled($profile->instagram_url)
            || filled($profile->facebook_url)
            || ($profile->show_email && filled($user->email))
        );

        // URL WhatsApp con messaggio precompilato
        $whatsappUrl = null;
        if ($profile?->show_whatsapp && $profile->allow_whatsapp_contact && $profile->whatsapp_number) {
            $whatsappUrl = 'https://wa.me/'
                . preg_replace('/\D+/', '', $profile->whatsapp_number)
                . '?text=' . urlencode('Ciao ' . $user->name . ', ti contatto dalla tua card su Kommunity.');
        }

        $cardUrl = route('card.show', $slug);

        // Pianeta attivo dell'utente (con fallback al singolo pianeta)
        $activePlanet = $user->activePlanet();

        // URL di registrazione con referral dell'utente della card,
        // incluso il pianeta (se disponibile) così il nuovo iscritto
        // viene associato direttamente a quel Pianeta.
        $referralUrl = $user->referralRegistrationUrl($activePlanet?->slug);

        // Auto-detect lingua dal browser del visitatore
        $locale = $request->getPreferredLanguage(['it', 'en', 'fr', 'es', 'de', 'ro']) ?? 'it';

        // Completezza profilo: il link al profilo pubblico Kommunity
        // viene mostrato solo se il profilo è completo almeno all'80%.
        $completion      = app(ProfileCompletionService::class)->calculate($user);
        $profileComplete = $completion['percentage'] >= 80;

        return view('card.show', compact(
            'onepage',
            'profile',
            'user',
            'hasProfileData',
            'whatsappUrl',
            'cardUrl',
            'locale',
            'activePlanet',
            'referralUrl',
            'profileComplete',
        ));
    }

    /**
     * Download del contatto in formato vCard (.vcf).
     * Compatibile con iPhone Rubrica, Android, Outlook, ecc.
     */
    public function vcard(string $slug): Response
    {
        $onepage = MemberOnepage::query()
            ->with(['user'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $user    = $onepage->user;
        $profile = $this->resolveProfile($user->id, ['profession', 'city']);

        // Separa nome e cognome (split sul primo spazio)
        $parts     = explode(' ', trim($user->name), 2);
        $firstName = $parts[0] ?? '';
        $lastName  = $parts[1] ?? '';

        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'N:' . $lastName . ';' . $firstName . ';;;',
            'FN:' . $user->name,
        ];

        if ($profile?->company_name) {
            $lines[] = 'ORG:' . $profile->company_name;
        }
        if ($profile?->profession?->name) {
            $lines[] = 'TITLE:' . $profile->profession->name;
        }
        if ($profile?->show_phone && $profile->phone) {
            $lines[] = 'TEL;TYPE=CELL:' . $profile->phone;
        }
        if ($profile?->show_email && $user->email) {
            $lines[] = 'EMAIL:' . $user->email;
        }
        if ($profile?->website) {
            $lines[] = 'URL:' . $profile->website;
        }
        if ($profile?->linkedin_url) {
            $lines[] = 'URL;type=LinkedIn:' . $profile->linkedin_url;
        }
        if ($profile?->city?->name) {
            $lines[] = 'ADR;TYPE=WORK:;;' . $profile->city->name . ';;;;';
        }

        $lines[] = 'NOTE:Profilo Kommunity: ' . route('card.show', $slug);
        $lines[] = 'END:VCARD';

        $vcf      = implode("\r\n", $lines) . "\r\n";
        $filename = Str::slug($user->name) . '.vcf';

        return response($vcf, 200, [
            'Content-Type'        => 'text/vcard; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Web App Manifest dinamico per la singola card (PWA "Aggiungi a Home").
     * Ogni card ha il proprio manifest con nome del membro e start_url
     * puntata alla card stessa, così l'icona installata apre direttamente
     * il biglietto da visita.
     */
    public function manifest(string $slug): \Illuminate\Http\JsonResponse
    {
        $onepage = MemberOnepage::query()
            ->with(['user'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $user    = $onepage->user;
        $cardUrl = '/card/' . $slug;

        $manifest = [
            'name'             => $user->name . ' — Kommunity Card',
            'short_name'       => Str::limit($user->name, 12, ''),
            'description'      => 'Biglietto da visita digitale di ' . $user->name . ' su Kommunity.',
            'id'               => $cardUrl,
            'start_url'        => $cardUrl,
            'scope'            => $cardUrl,
            'display'          => 'standalone',
            'orientation'      => 'portrait',
            'background_color' => '#0b1f17',
            'theme_color'      => '#0b1f17',
            'lang'             => 'it',
            'dir'              => 'ltr',
            'icons'            => [
                [
                    'src'     => asset('images/icon-192.png'),
                    'sizes'   => '192x192',
                    'type'    => 'image/png',
                    'purpose' => 'any maskable',
                ],
                [
                    'src'     => asset('images/icon-512.png'),
                    'sizes'   => '512x512',
                    'type'    => 'image/png',
                    'purpose' => 'any maskable',
                ],
            ],
        ];

        return response()->json($manifest, 200, [
            'Content-Type'  => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Seleziona in modo deterministico il profilo "buono" di un utente.
     *
     * In presenza di più righe member_profiles per lo stesso user_id
     * (duplicati storici, omonimie con doppia registrazione) preferisce
     * sempre la riga ATTIVA e con id più alto (la più recente), evitando
     * che la card agganci per sbaglio un profilo vuoto/inattivo.
     *
     * @param  array<int, string>  $with  Relazioni da eager-loadare.
     */
    private function resolveProfile(int $userId, array $with = []): ?MemberProfile
    {
        return MemberProfile::query()
            ->with($with)
            ->where('user_id', $userId)
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->first();
    }
}
