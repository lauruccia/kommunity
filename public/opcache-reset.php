<?php
// File temporaneo per debug — DA ELIMINARE DOPO L'USO
$base   = '/home2/kommunity/kommunity';
$apiKey = null;
$model  = null;

// Legge .env
$env = @file_get_contents($base . '/.env');
if ($env) {
    foreach (explode("\n", $env) as $line) {
        $line = trim($line);
        if (str_starts_with($line, 'OPENAI_API_KEY='))
            $apiKey = trim(substr($line, strpos($line, '=') + 1), " \t\r\n\"'");
        if (str_starts_with($line, 'OPENAI_PROFILE_MODEL='))
            $model = trim(substr($line, strpos($line, '=') + 1), " \t\r\n\"'");
    }
}

// Legge modello dal config PHP se non in .env
if (!$model) {
    $cfg = @file_get_contents($base . '/config/services.php');
    if ($cfg && preg_match("/'model'\s*=>\s*env\([^,]+,\s*'([^']+)'\)/", $cfg, $m)) {
        $model = $m[1] . ' (da config default)';
    }
}

echo '<pre>';
echo "Chiave : " . ($apiKey ? substr($apiKey,0,10).'***' : 'NON TROVATA') . "\n";
echo "Modello: " . ($model ?: 'non determinato') . "\n\n";

if (!$apiKey) { echo 'ERRORE: chiave non trovata'; exit; }

// Lista modelli disponibili (v1)
echo "=== MODELLI DISPONIBILI (v1) ===\n";
$url = "https://generativelanguage.googleapis.com/v1/models?key={$apiKey}";
$ctx = stream_context_create(['http' => ['timeout' => 10, 'ignore_errors' => true]]);
$res = @file_get_contents($url, false, $ctx);
$data = $res ? json_decode($res, true) : null;
if (!empty($data['models'])) {
    foreach ($data['models'] as $m) {
        $methods = implode(', ', $m['supportedGenerationMethods'] ?? []);
        if (str_contains($methods, 'generateContent')) {
            echo '  ✅ ' . $m['name'] . "\n";
        }
    }
} else {
    echo "Errore o nessun modello: " . substr($res ?: '(nessuna risposta)', 0, 200) . "\n";
}

// Test chiamata reale con il modello rilevato (solo nome base senza " (da config default)")
$modelName = explode(' ', $model)[0];
echo "\n=== TEST CHIAMATA con '$modelName' su v1 ===\n";
$testUrl = "https://generativelanguage.googleapis.com/v1/models/{$modelName}:generateContent?key={$apiKey}";
$payload  = json_encode(['contents' => [['parts' => [['text' => 'Rispondi solo con: {"ok":true}']]]]]);
$ctx2 = stream_context_create(['http' => [
    'method' => 'POST', 'header' => "Content-Type: application/json\r\n",
    'content' => $payload, 'timeout' => 10, 'ignore_errors' => true,
]]);
$res2 = @file_get_contents($testUrl, false, $ctx2);
preg_match('/HTTP\/\S+ (\d+)/', $http_response_header[0] ?? '', $hm);
echo '[' . ($hm[1] ?? '?') . '] ' . substr($res2 ?: '(nessuna risposta)', 0, 200) . "\n";

echo '</pre>';
