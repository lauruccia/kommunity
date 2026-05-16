<?php

namespace Tests\Unit;

use App\Models\MemberProfile;
use App\Services\ProfileAiRewriteService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ProfileAiRewriteServiceTest extends TestCase
{
    public function test_it_returns_original_fields_when_openai_is_not_configured(): void
    {
        config(['services.openai.api_key' => null]);

        $result = app(ProfileAiRewriteService::class)->rewrite(new MemberProfile(), [
            'short_bio' => 'Commercialista per PMI',
            'bio' => 'Aiuto le imprese.',
            'services' => 'Bilanci, dichiarazioni',
            'skills' => 'Fiscalita, controllo',
            'networking_goals' => '',
        ]);

        $this->assertSame('Commercialista per PMI', $result['short_bio']);
        $this->assertSame('Aiuto le imprese.', $result['bio']);
        $this->assertNull($result['networking_goals']);
    }

    public function test_it_uses_responses_api_output_text(): void
    {
        config([
            'services.openai.api_key' => 'test-key',
            'services.openai.model' => 'gpt-5.2',
            'services.openai.timeout' => 30,
        ]);

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response([
                'output_text' => json_encode([
                    'short_bio' => 'Consulente fiscale per PMI e professionisti.',
                    'bio' => 'Affianco imprese e professionisti nella gestione fiscale con un approccio chiaro e operativo.',
                    'services' => 'Consulenza fiscale, bilanci e pianificazione.',
                    'skills' => 'Fiscalita, bilancio, controllo di gestione.',
                    'networking_goals' => 'Conoscere imprenditori con cui costruire collaborazioni concrete.',
                ]),
            ]),
        ]);

        $result = app(ProfileAiRewriteService::class)->rewrite(new MemberProfile(), [
            'short_bio' => 'faccio tasse',
            'bio' => 'aiuto aziende',
            'services' => 'bilanci',
            'skills' => 'fisco',
            'networking_goals' => 'conoscere aziende',
        ]);

        $this->assertSame('Consulente fiscale per PMI e professionisti.', $result['short_bio']);
        $this->assertSame('Consulenza fiscale, bilanci e pianificazione.', $result['services']);

        Http::assertSent(fn ($request) => $request->url() === 'https://api.openai.com/v1/responses'
            && $request['model'] === 'gpt-5.2');
    }

    public function test_it_sends_profile_context_for_short_inputs(): void
    {
        config([
            'services.openai.api_key' => 'test-key',
            'services.openai.model' => 'gpt-5.2',
        ]);

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response([
                'output_text' => json_encode([
                    'short_bio' => 'Architetto specializzato in spazi residenziali funzionali.',
                    'bio' => 'Progetta ambienti residenziali valorizzando esigenze, stile di vita e contesto locale.',
                    'services' => 'Progettazione, consulenza e direzione lavori.',
                    'skills' => 'Architettura residenziale, progettazione interni.',
                    'networking_goals' => 'Entrare in contatto con imprese e professionisti del territorio.',
                ]),
            ]),
        ]);

        app(ProfileAiRewriteService::class)->rewrite(new MemberProfile(), [
            'short_bio' => 'architetto',
            'bio' => '',
            'services' => '',
            'skills' => '',
            'networking_goals' => '',
        ], [
            'azienda' => 'Studio Rossi',
            'professioni' => 'Architetto',
            'categorie' => 'Casa e costruzioni',
            'citta' => 'Roma',
        ]);

        Http::assertSent(function ($request): bool {
            $input = json_decode($request['input'], true);

            return ($input['contesto_profilo']['azienda'] ?? null) === 'Studio Rossi'
                && ($input['contesto_profilo']['professioni'] ?? null) === 'Architetto'
                && ($input['testi_da_rielaborare']['short_bio'] ?? null) === 'architetto';
        });
    }

    public function test_it_keeps_original_fields_when_api_fails(): void
    {
        config(['services.openai.api_key' => 'test-key']);

        Http::fake([
            'https://api.openai.com/v1/responses' => Http::response(['error' => 'failed'], 500),
        ]);

        $result = app(ProfileAiRewriteService::class)->rewrite(new MemberProfile(), [
            'short_bio' => 'Originale',
            'bio' => 'Bio originale',
            'services' => 'Servizi originali',
            'skills' => 'Skill originali',
            'networking_goals' => 'Obiettivi originali',
        ]);

        $this->assertSame('Originale', $result['short_bio']);
        $this->assertSame('Bio originale', $result['bio']);
    }
}
