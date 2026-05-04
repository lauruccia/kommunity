<?php
/**
 * migrate-media.php — Script ONE-TIME per spostare i file media
 * da storage/app/public/ a public_html/media/
 *
 * ISTRUZIONI:
 *   1. Carica questo file in public_html/ tramite cPanel File Manager
 *   2. Apri nel browser: https://kommunity.it/migrate-media.php?token=km2026migrate
 *   3. Controlla l'output
 *   4. ELIMINA questo file immediatamente dopo l'uso!
 */

// ── Protezione: token segreto ─────────────────────────────────────────────────
define('SECRET_TOKEN', 'km2026migrate');

if (($_GET['token'] ?? '') !== SECRET_TOKEN) {
    http_response_code(403);
    die('Accesso negato. Aggiungi ?token=' . SECRET_TOKEN . ' all\'URL.');
}

// ── Rilevamento percorsi ──────────────────────────────────────────────────────
// __DIR__ = /home2/USERNAME/public_html (dove gira lo script)
$publicHtml = rtrim(__DIR__, '/');               // /home2/USERNAME/public_html
$homeDir    = dirname($publicHtml);              // /home2/USERNAME

// Cerca la cartella Laravel: prova prima il nome "kommunity", poi scansiona
$appRoot = null;
foreach (['kommunity', 'app', 'laravel', 'web'] as $candidate) {
    $try = $homeDir . '/' . $candidate;
    if (is_dir($try . '/storage/app/public')) {
        $appRoot = $try;
        break;
    }
}

// Fallback: scansiona tutte le sottocartelle di $homeDir
if (! $appRoot) {
    foreach (glob($homeDir . '/*/storage/app/public', GLOB_ONLYDIR) as $found) {
        $appRoot = dirname(dirname(dirname($found)));
        break;
    }
}

if (! $appRoot) {
    die('<b>Errore:</b> impossibile trovare la cartella Laravel con storage/app/public/.');
}

$source = $appRoot . '/storage/app/public';   // sorgente
$target = $publicHtml . '/media';             // destinazione

// ── Helper ───────────────────────────────────────────────────────────────────
function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function copyTree(string $src, string $dst, array &$log): void
{
    if (! is_dir($src)) return;

    if (! is_dir($dst)) {
        if (! mkdir($dst, 0755, true)) {
            $log['errors'][] = "Impossibile creare directory: $dst";
            return;
        }
    }

    foreach (new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    ) as $item) {
        $relative  = substr($item->getPathname(), strlen($src) + 1);
        $destPath  = $dst . '/' . $relative;

        if ($item->isDir()) {
            if (! is_dir($destPath)) mkdir($destPath, 0755, true);
            continue;
        }

        // Se il file esiste già nella destinazione, salta
        if (file_exists($destPath)) {
            $log['skipped'][] = $relative;
            continue;
        }

        if (copy($item->getPathname(), $destPath)) {
            chmod($destPath, 0644);
            $log['copied'][] = $relative;
        } else {
            $log['errors'][] = "Copia fallita: $relative";
        }
    }
}

// ── Esecuzione ────────────────────────────────────────────────────────────────
$log = ['copied' => [], 'skipped' => [], 'errors' => []];

if (! is_dir($source)) {
    $log['errors'][] = "Sorgente non trovata: $source";
} else {
    copyTree($source, $target, $log);
}

// ── Output HTML ───────────────────────────────────────────────────────────────
?><!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Migrazione media — Kommunity</title>
<style>
  body { font-family: monospace; max-width: 900px; margin: 2rem auto; padding: 1rem; background: #0f172a; color: #e2e8f0; }
  h1   { color: #8bc53f; }
  h2   { color: #5eead4; margin-top: 1.5rem; }
  .box { background: #1e293b; border: 1px solid #334155; border-radius: .5rem; padding: 1rem; margin: .5rem 0; font-size: .85rem; }
  .ok  { color: #86efac; }
  .warn{ color: #fcd34d; }
  .err { color: #fca5a5; }
  .path{ color: #94a3b8; font-size: .75rem; }
  ul   { margin: .25rem 0; padding-left: 1.2rem; }
  li   { line-height: 1.7; }
</style>
</head>
<body>
<h1>🗂 Migrazione file media — Kommunity</h1>

<div class="box">
  <p class="path"><b>Cartella Laravel:</b> <?= h($appRoot) ?></p>
  <p class="path"><b>Sorgente:</b>         <?= h($source) ?></p>
  <p class="path"><b>Destinazione:</b>     <?= h($target) ?></p>
</div>

<h2>✅ File copiati (<?= count($log['copied']) ?>)</h2>
<div class="box">
<?php if ($log['copied']): ?>
  <ul>
    <?php foreach ($log['copied'] as $f): ?>
      <li class="ok"><?= h($f) ?></li>
    <?php endforeach; ?>
  </ul>
<?php else: ?>
  <p class="warn">Nessun file copiato (forse erano già tutti presenti).</p>
<?php endif; ?>
</div>

<h2>⏭ File già presenti / saltati (<?= count($log['skipped']) ?>)</h2>
<div class="box">
<?php if ($log['skipped']): ?>
  <ul>
    <?php foreach ($log['skipped'] as $f): ?>
      <li class="warn"><?= h($f) ?></li>
    <?php endforeach; ?>
  </ul>
<?php else: ?>
  <p class="ok">Nessuno.</p>
<?php endif; ?>
</div>

<h2>❌ Errori (<?= count($log['errors']) ?>)</h2>
<div class="box">
<?php if ($log['errors']): ?>
  <ul>
    <?php foreach ($log['errors'] as $e): ?>
      <li class="err"><?= h($e) ?></li>
    <?php endforeach; ?>
  </ul>
<?php else: ?>
  <p class="ok">Nessun errore.</p>
<?php endif; ?>
</div>

<?php if (! $log['errors']): ?>
<div class="box" style="border-color:#8bc53f;margin-top:1.5rem;">
  <p class="ok"><b>✔ Migrazione completata senza errori.</b></p>
  <p class="warn">⚠ Ora aggiungi <code>MEDIA_DISK_ROOT=<?= h($target) ?></code> al file <code>.env</code> nella cartella Laravel.</p>
  <p class="err"><b>⚠ ELIMINA SUBITO questo file da public_html/!</b></p>
</div>
<?php endif; ?>

</body>
</html>
