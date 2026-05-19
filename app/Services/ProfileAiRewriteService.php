<?php

namespace App\Services;

use App\Models\MemberProfile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProfileAiRewriteService
{
    private ?string $lastError = null;

    public function getLastError(): ?string
    {
        return $this->lastError;
    }
    /**
     * @param array<string, mixed> $fields
     * @param array<string, mixed> $context
     * @return array<string, string|null>
     */
    public function rewrite(MemberProfile $profile, array $fields, array $context = []): array
    {
        if (! $this->isConfigured()) {
            return $this->normalize($fields);
        }

        $model   = (string) config('services.openai.model', 'gemini-2.0-flash');
        $apiKey  = (string) config('services.openai.api_key');
        $timeout = (int) config('services.openai.timeout', 45);

        $endpoint = 'https://generativelanguage.googleapis.com/v1/models/'
            . $model
            . ':generateContent?key='
            . $apiKey;

        $payload = [
            'systemInstruction' => [
                'parts' => [['text' => $this->instructions()]],
            ],
            'contents' => [
                [
                    'parts' => [['text' => $this->buildInput($profile, $fields, $context)]],
                ],
            ],
        ];

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout($timeout)
                ->post($endpoint, $payload);

            if (! $response->successful()) {
                $this->lastError = 'HTTP '.$response->status().': '.Str::limit($response->body(), 300);
                Log::warning('Rielaborazione AI profilo fallita (Gemini)', [
                    'profile_id' => $profile->getKey(),
                    'status'     => $response->status(),
                    'body'       => Str::limit($response->body(), 500),
                ]);

                return $this->normalize($fields);
            }

            return $this->mergeRewrittenFields($fields, $this->extractOutputText($response->json()));

        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            Log::warning('Rielaborazione AI profilo non disponibile (Gemini)', [
                'profile_id' => $profile->getKey(),
                'error'      => $e->getMessage(),
            ]);

            return $this->normalize($fields);
        }
    }

    public function isConfigured(): bool
    {
        return filled(config('services.openai.api_key'));
    }

    private function instructions(): string
    {
        return <<<'PROMPT'
Sei un copywriter B2B italiano per Kommunity, una piattaforma professionale di networking.
Il tuo compito è compilare o rielaborare i testi del profilo professionale in modo chiaro, concreto e avvincente.

Regole fondamentali:
- Non inventare competenze, clienti, risultati, certificazioni, numeri o promesse non presenti nel contesto.
- Se un campo è null o vuoto, generalo comunque partendo esclusivamente dalle informazioni di contesto disponibili (nome, azienda, professione, città, settore, sito web, LinkedIn). Un campo vuoto non è un'eccezione: è un campo da costruire da zero con ciò che sai.
- Se un campo contiene già del testo, rielaboralo in modo più chiaro e professionale senza stravolgerne il significato.
- Usa la prima persona se il testo originale la usa; altrimenti usa una terza persona naturale.
- Evita tono pubblicitario generico, superlativi vuoti, emoji e frasi come "leader di settore" o "appassionato di".
- Scrivi in italiano.

Rispondi SOLO con un oggetto JSON valido, senza markdown, senza ```json, senza testo prima o dopo.
L'oggetto deve avere esattamente queste chiavi: short_bio, bio, services, skills, networking_goals.
PROMPT;
    }

    /**
     * @param array<string, mixed> $fields
     * @param array<string, mixed> $context
     */
    private function buildInput(MemberProfile $profile, array $fields, array $context): string
    {
        $profileContext = array_filter(array_merge([
            'nome'        => $profile->user?->name,
            'azienda'     => $profile->company_name,
            'professione' => $profile->profession?->name,
            'citta'       => $profile->city?->name,
        ], $context), fn ($value) => filled($value));

        $normalized = $this->normalize($fields);
        $hasAnyText = collect($normalized)->filter(fn ($v) => filled($v))->isNotEmpty();

        return json_encode([
            'contesto_profilo' => $profileContext,
            'istruzione'       => $hasAnyText
                ? 'Rielabora i testi presenti e genera quelli mancanti (null) usando il contesto.'
                : 'Tutti i campi testo sono vuoti. Generali da zero usando esclusivamente il contesto del profilo.',
            'testi'            => $normalized,
            'limiti_caratteri' => [
                'short_bio'        => 500,
                'bio'              => 3000,
                'services'         => 3000,
                'skills'           => 2000,
                'networking_goals' => 2000,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param array<string, mixed> $fields
     * @return array<string, string|null>
     */
    private function normalize(array $fields): array
    {
        return collect(['short_bio', 'bio', 'services', 'skills', 'networking_goals'])
            ->mapWithKeys(fn (string $field): array => [$field => $this->clean($fields[$field] ?? null)])
            ->all();
    }

    private function clean(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * Estrae il testo JSON dalla risposta Gemini.
     * Struttura: candidates[0].content.parts[0].text
     *
     * @param array<string, mixed>|null $response
     */
    private function extractOutputText(?array $response): ?string
    {
        if (! $response) {
            return null;
        }

        // Gemini: candidates[0].content.parts[0].text
        $text = Arr::get($response, 'candidates.0.content.parts.0.text');

        if (is_string($text) && trim($text) !== '') {
            return $text;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $original
     * @return array<string, string|null>
     */
    private function mergeRewrittenFields(array $original, ?string $output): array
    {
        $normalized = $this->normalize($original);

        if (! $output) {
            return $normalized;
        }

        $decoded = json_decode($this->stripJsonFences($output), true);

        if (! is_array($decoded)) {
            return $normalized;
        }

        $limits = [
            'short_bio'        => 500,
            'bio'              => 3000,
            'services'         => 3000,
            'skills'           => 2000,
            'networking_goals' => 2000,
        ];

        foreach ($limits as $field => $limit) {
            $value = $this->clean(Arr::get($decoded, $field));
            if ($value !== null) {
                $normalized[$field] = Str::limit($value, $limit, '');
            }
        }

        return $normalized;
    }

    private function stripJsonFences(string $text): string
    {
        $text = trim($text);

        if (str_starts_with($text, '```')) {
            $text = preg_replace('/^```(?:json)?\s*/i', '', $text) ?? $text;
            $text = preg_replace('/\s*```$/', '', $text) ?? $text;
        }

        return trim($text);
    }
}
