<?php
// File temporaneo per debug — DA ELIMINARE DOPO L'USO
$base   = '/home2/kommunity/kommunity';
$apiKey = null;

// Legge OPENAI_API_KEY dal .env
$env = @file_get_contents($base . '/.env');
if ($env) {
    foreach (explode("\n", $env) as $line) {
        if (str_starts_with(trim($line), 'OPENAI_API_KEY=')) {
            $apiKey = trim(substr($line, strpos($line, '=') + 1), " \t\r\n\"'");
            break;
        }
    }
}

echo '<pre>';
echo 'Chiave in uso: ' . ($apiKey ? substr($apiKey, 0, 10) . '***' : 'NON TROVATA') . "\n\n";

if (!$apiKey) {
    echo 'ERRORE: OPENAI_API_KEY non trovata nel .env';
    exit;
}

// Prova i due endpoint (v1 e v1beta) con due modelli diversi
$tests = [
    'v1 + gemini-1.5-flash'       => "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key={$apiKey}",
    'v1beta + gemini-1.5-flash'   => "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}",
    'v1beta + gemini-2.0-flash'   => "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}",
    'v1beta + gemini-2.5-flash'   => "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}",
    'v1beta + gemini-2.5-flash-preview-05-20' => "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-preview-05-20:generateContent?key={$apiKey}",
];

$payload = json_encode([
    'contents' => [['parts' => [['text' => 'Rispondi solo con: {"ok":true}']]]],
    'generationConfig' => ['responseMimeType' => 'application/json'],
]);

foreach ($tests as $label => $url) {
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\n",
            'content' => $payload,
            'timeout' => 10,
            'ignore_errors' => true,
        ],
    ]);
    $result = @file_get_contents($url, false, $ctx);
    $code   = 'ERR';
    if (isset($http_response_header)) {
        preg_match('/HTTP\/\S+ (\d+)/', $http_response_header[0], $m);
        $code = $m[1] ?? '?';
    }
    $short = $result ? substr(strip_tags($result), 0, 120) : '(nessuna risposta)';
    echo "[{$code}] {$label}\n      {$short}\n\n";
}

echo '</pre>';
