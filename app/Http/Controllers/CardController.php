<?php

namespace App\Http\Controllers;

use App\Models\MemberOnepage;
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
            ->with([
                'user.memberProfile.professions',
                'user.memberProfile.profession',
                'user.memberProfile.city',
                'user.memberProfile.chapter',
            ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $profile = $onepage->user->memberProfile;
        $user    = $onepage->user;

        // URL WhatsApp con messaggio precompilato
        $whatsappUrl = null;
        if ($profile?->show_whatsapp && $profile->allow_whatsapp_contact && $profile->whatsapp_number) {
            $whatsappUrl = 'https://wa.me/'
                . preg_replace('/\D+/', '', $profile->whatsapp_number)
                . '?text=' . urlencode('Ciao ' . $user->name . ', ti contatto dalla tua card su Kommunity.');
        }

        $cardUrl = route('card.show', $slug);

        // Auto-detect lingua dal browser del visitatore
        $locale = $request->getPreferredLanguage(['it', 'en', 'fr', 'es', 'de', 'ro']) ?? 'it';

        return view('card.show', compact('onepage', 'profile', 'user', 'whatsappUrl', 'cardUrl', 'locale'));
    }

    /**
     * Download del contatto in formato vCard (.vcf).
     * Compatibile con iPhone Rubrica, Android, Outlook, ecc.
     */
    public function vcard(string $slug): Response
    {
        $onepage = MemberOnepage::query()
            ->with([
                'user.memberProfile.profession',
                'user.memberProfile.city',
            ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $profile = $onepage->user->memberProfile;
        $user    = $onepage->user;

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
}
