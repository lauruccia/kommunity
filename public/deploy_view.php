<?php
$root    = "/home2/kommunity/kommunity";
$encoded = file_get_contents(__DIR__ . "/deploy_content.txt");
if (!$encoded) { die("Errore: deploy_content.txt non trovato."); }

$content = base64_decode(trim($encoded));
$target  = $root . "/resources/views/members/show.blade.php";

// 1. Scrivi il file
$written = file_put_contents($target, $content);

// 2. Svuota view cache
$viewDir = $root . "/storage/framework/views";
$deleted = 0;
foreach (glob($viewDir . "/*.php") ?: [] as $f) {
    @unlink($f);
    $deleted++;
}

// 3. Reset OPcache se disponibile
$opcache = false;
if (function_exists('opcache_reset')) {
    $opcache = opcache_reset();
}

// 4. Output
echo "<style>body{font-family:sans-serif;padding:2rem;max-width:700px}</style>";
echo "<h2>" . ($written ? "✅ File scritto" : "❌ Errore scrittura") . "</h2>";
echo "<ul>";
echo "<li>Bytes scritti: <b>{$written}</b> (attesi: 23413)</li>";
echo "<li>View cache: <b>{$deleted}</b> file eliminati</li>";
echo "<li>OPcache reset: <b>" . ($opcache ? 'OK' : (function_exists('opcache_reset') ? 'FALLITO' : 'non disponibile')) . "</b></li>";
echo "<li>Dimensione file sul disco: <b>" . filesize($target) . "</b> bytes</li>";
echo "<li>Fine file: <b>" . htmlspecialchars(substr(file_get_contents($target), -40)) . "</b></li>";
echo "</ul>";
echo "<p><a href='/member/federico-drago'>Testa la pagina</a> &mdash; poi elimina deploy_view.php e deploy_content.txt</p>";
