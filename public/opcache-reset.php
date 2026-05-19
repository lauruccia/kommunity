<?php
// File temporaneo per debug deploy — DA ELIMINARE DOPO L'USO
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache svuotato.<br>";
} else {
    echo "ℹ️ OPcache non attivo su questo server.<br>";
}

// Mostra l'ora di modifica dei file chiave per verificare il deploy
$files = [
    'ProfileController'    => dirname(__DIR__) . '/app/Http/Controllers/ProfileController.php',
    'AiRewriteService'     => dirname(__DIR__) . '/app/Services/ProfileAiRewriteService.php',
    'services.php (config)'=> dirname(__DIR__) . '/config/services.php',
];

echo "<br><strong>Timestamp file sul server:</strong><br>";
foreach ($files as $label => $path) {
    if (file_exists($path)) {
        echo htmlspecialchars($label) . ': ' . date('Y-m-d H:i:s', filemtime($path)) . '<br>';
    } else {
        echo htmlspecialchars($label) . ': FILE NON TROVATO<br>';
    }
}

echo "<br><strong>Cerca nel controller:</strong><br>";
$controller = file_get_contents(dirname(__DIR__) . '/app/Http/Controllers/ProfileController.php');
echo strpos($controller, 'AI profilo: verifica rewrite') !== false
    ? '✅ Codice nuovo presente nel controller'
    : '❌ Codice nuovo NON trovato — deploy non aggiornato';
