<?php

namespace App\Services;

use App\Models\MemberProfile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProfileAiRewriteService
{
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

        $payload = [
            'model' => (string) config('services.openai.model', 'gpt-5.2'),
            'instructions' => $this->instructions(),
            'input' => $this->buildInput($profile, $fields, $context),
        ];

        try {
            $response = Http::withToken((string) config('services.openai.api_key'))
                ->acceptJson()
                ->asJson()
                ->timeout((int) config('services.openai.timeout', 30))
                ->post('https://api.openai.com/v1/responses', $payload);

            if (! $response->successful()) {
                Log::warning('Rielaborazione AI profilo fallita', [
                    'profile_id' => $profile->getKey(),
                    'status' => $response->status(),
                    'body' => Str::limit($response->body(), 500),
                ]);

                return $this->normalize($fields);
            }

            return $this->mergeRewrittenFields($fields, $this->extractOutputText($response->json()));
        } catch (\Throwable $e) {
            Log::warning('Rielaborazione AI profilo non disponibile', [
                'profile_id' => $profile->getKey(),
                'error' => $e->getMessage(),
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
Rielabora i testi del profilo in modo chiaro, concreto e avvincente, senza inventare competenze, clienti, risultati, certificazioni, numeri o promesse.
Anche se un campo contiene solo una parola o una frase molto breve, usa tutto il contesto del profilo per trasformarlo in un testo utile e professionale.
Mantieni la prima persona se il testo originale la usa, altrimenti usa una terza persona naturale.
Evita tono pubblicitario generico, superlativi vuoti, emoji e frasi come "leader di settore".
Rispondi solo con JSON valido con queste chiavi: short_bio, bio, services, skills, networking_goals.
PROMPT;
    }

    /**
     * @param array<string, mixed> $fields
     * @param array<string, mixed> $context
     */
    private function buildInput(MemberProfile $profile, array $fields, array $context): string
    {
        $profileContext = array_filter(array_merge([
            'nome' => $profile->user?->name,
            'azienda' => $profile->company_name,
            'professione' => $profile->profession?->name,
            'citta' => $profile->city?->name,
        ], $context), fn ($value) => filled($value));

        return json_encode([
            'contesto_profilo' => $profileContext,
            'testi_da_rielaborare' => $this->normalize($fields),
            'limiti' => [
                'short_bio' => 'massimo 500 caratteri',
                'bio' => 'massimo 3000 caratteri',
                'services' => 'massimo 3000 caratteri',
                'skills' => 'massimo 2000 caratteri',
                'networking_goals' => 'massimo 2000 caratteri',
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
     * @param array<string, mixed>|null $response
     */
    private function extractOutputText(?array $response): ?string
    {
        if (! $response) {
            return null;
        }

        if (is_string($response['output_text'] ?? null)) {
            return $response['output_text'];
        }

        foreach ((array) ($response['output'] ?? []) as $item) {
            foreach ((array) ($item['content'] ?? []) as $content) {
                $text = $content['text'] ?? null;
                if (is_string($text) && trim($text) !== '') {
                    return $text;
                }
            }
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
            'short_bio' => 500,
            'bio' => 3000,
            'services' => 3000,
            'skills' => 2000,
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
