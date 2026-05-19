<?php
// File temporaneo per debug deploy — DA ELIMINARE DOPO L'USO
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache svuotato.<br>";
} else {
    echo "ℹ️ OPcache non attivo su questo server.<br>";
}

// Base path Laravel su cPanel
$base = '/home2/kommunity/kommunity';

$files = [
    'ProfileController'     => $base . '/app/Http/Controllers/ProfileController.php',
    'AiRewriteService'      => $base . '/app/Services/ProfileAiRewriteService.php',
    'services.php (config)' => $base . '/config/services.php',
];

echo "<br><strong>Timestamp file sul server:</strong><br>";
foreach ($files as $label => $path) {
    if (file_exists($path)) {
        echo htmlspecialchars($label) . ': ' . date('Y-m-d H:i:s', filemtime($path)) . '<br>';
    } else {
        echo htmlspecialchars($label) . ': FILE NON TROVATO<br>';
    }
}

echo "<br><strong>Codice nuovo nel controller:</strong><br>";
$controller = @file_get_contents($base . '/app/Http/Controllers/ProfileController.php');
if ($controller === false) {
    echo "❌ Impossibile leggere il file (permessi?)<br>";
} else {
    echo strpos($controller, 'AI profilo: verifica rewrite') !== false
        ? '✅ Codice nuovo presente — deploy OK'
        : '❌ Codice vecchio — deploy NON aggiornato';
}

echo "<br><br><strong>Contenuto .env (solo righe AI/OpenAI):</strong><br>";
$env = @file_get_contents($base . '/.env');
if ($env) {
    foreach (explode("\n", $env) as $line) {
        if (stripos($line, 'openai') !== false || stripos($line, 'gemini') !== false) {
            // Maschera la chiave lasciando solo i primi 8 caratteri
            $masked = preg_replace('/=(.{8}).*/', '=$1***', $line);
            echo htmlspecialchars($masked) . '<br>';
        }
    }
} else {
    echo "❌ .env non leggibile<br>";
}
