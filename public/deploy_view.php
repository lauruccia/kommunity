<?php
$root = "/home2/kommunity/kommunity";
$encoded = file_get_contents(__DIR__ . "/deploy_content.txt");
if (!$encoded) { die("Errore: deploy_content.txt non trovato."); }
$content = base64_decode(trim($encoded));
$target  = $root . "/resources/views/members/show.blade.php";
if (file_put_contents($target, $content)) {
    foreach (glob($root . "/storage/framework/views/*.php") ?: [] as $f) { @unlink($f); }
    echo "<h2 style='font-family:sans-serif;padding:2rem'>&#10003; OK! File aggiornato (" . strlen($content) . " bytes). Cache svuotata.</h2>";
    echo "<p style='font-family:sans-serif;padding:0 2rem'><a href='/member/federico-drago'>Testa la pagina</a> &mdash; poi elimina deploy_view.php e deploy_content.txt</p>";
} else {
    echo "<h2 style='font-family:sans-serif;padding:2rem'>&#10007; Errore scrittura: " . htmlspecialchars($target) . "</h2>";
}
