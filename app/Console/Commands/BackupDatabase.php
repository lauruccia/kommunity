<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Backup database compatibile con hosting condiviso (no shell, no mysqldump).
 *
 * Genera un file .sql in storage/app/backups/ contenente CREATE TABLE + INSERT
 * di tutte le tabelle del database principale. Usa solo PDO/Schema, niente
 * shell_exec().
 *
 * Schedulazione via cPanel cron (consigliata):
 *     0 3 * * *   cd /home/USER/public_html && /usr/local/bin/php artisan app:db-backup
 *
 * Mantiene gli ultimi N backup (default 7) cancellando i più vecchi.
 */
class BackupDatabase extends Command
{
    protected $signature = 'app:db-backup
                            {--keep=7 : Numero di backup recenti da mantenere}
                            {--path=backups : Sottocartella relativa a storage/app/}';

    protected $description = 'Esegue un backup SQL del database (compatibile hosting condiviso, no shell)';

    public function handle(): int
    {
        $connection = DB::connection();
        $driver     = $connection->getDriverName();

        if ($driver !== 'mysql') {
            $this->error("Backup supportato solo su MySQL/MariaDB. Driver corrente: {$driver}");
            return self::FAILURE;
        }

        $database = $connection->getDatabaseName();
        $folder   = trim((string) $this->option('path'), '/');
        $stamp    = now()->format('Y-m-d_His');
        $filename = "{$folder}/{$database}_{$stamp}.sql";

        // Assicura la directory
        $dir = storage_path('app/' . $folder);
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $absolutePath = storage_path('app/' . $filename);
        $handle       = fopen($absolutePath, 'w');

        if (! $handle) {
            $this->error("Impossibile aprire {$absolutePath} in scrittura.");
            return self::FAILURE;
        }

        fwrite($handle, "-- Kommunity DB Backup\n");
        fwrite($handle, "-- Database: {$database}\n");
        fwrite($handle, '-- Generato: ' . now()->toDateTimeString() . "\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET NAMES utf8mb4;\n\n");

        $tables = $connection->select('SHOW TABLES');
        $key    = 'Tables_in_' . $database;

        $totalRows = 0;
        foreach ($tables as $row) {
            $table = $row->{$key} ?? null;
            if (! $table) {
                continue;
            }

            $this->line(" → {$table}");

            // CREATE TABLE
            $createRow = $connection->select("SHOW CREATE TABLE `{$table}`")[0] ?? null;
            $createSql = $createRow->{'Create Table'} ?? null;
            if ($createSql) {
                fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
                fwrite($handle, $createSql . ";\n\n");
            }

            // INSERT — chunked per evitare di caricare tutta la tabella in RAM
            $chunkSize = 500;
            $rows = [];
            $count = 0;

            $connection->table($table)->orderBy(
                $this->primaryKeyOrFirstColumn($connection, $table)
            )->chunk($chunkSize, function ($chunk) use ($handle, $table, &$count) {
                if ($chunk->isEmpty()) {
                    return;
                }

                $columns = array_keys((array) $chunk->first());
                $colList = '`' . implode('`,`', $columns) . '`';

                $values = [];
                foreach ($chunk as $record) {
                    $rec = (array) $record;
                    $vals = array_map(function ($v) {
                        if ($v === null) {
                            return 'NULL';
                        }
                        if (is_int($v) || is_float($v)) {
                            return (string) $v;
                        }
                        return "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], (string) $v) . "'";
                    }, array_values($rec));
                    $values[] = '(' . implode(',', $vals) . ')';
                }

                fwrite($handle, "INSERT INTO `{$table}` ({$colList}) VALUES\n");
                fwrite($handle, implode(",\n", $values) . ";\n\n");
                $count += $chunk->count();
            });

            $totalRows += $count;
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        $sizeMb = round(filesize($absolutePath) / 1024 / 1024, 2);
        $this->info("Backup completato: {$filename} ({$sizeMb} MB, {$totalRows} righe)");

        // Pulizia backup vecchi
        $this->pruneOldBackups($folder, (int) $this->option('keep'));

        return self::SUCCESS;
    }

    /**
     * Restituisce la primary key della tabella (o, in mancanza, la prima colonna).
     * Serve a chunk(): senza ORDER BY i risultati sono indeterminati.
     */
    protected function primaryKeyOrFirstColumn($connection, string $table): string
    {
        $columns = $connection->select("SHOW COLUMNS FROM `{$table}`");
        foreach ($columns as $col) {
            if (($col->Key ?? '') === 'PRI') {
                return $col->Field;
            }
        }
        return $columns[0]->Field ?? 'id';
    }

    /**
     * Mantiene gli ultimi N file .sql nella cartella backup, elimina i più vecchi.
     */
    protected function pruneOldBackups(string $folder, int $keep): void
    {
        if ($keep <= 0) {
            return;
        }

        $dir   = storage_path('app/' . $folder);
        $files = collect(File::files($dir))
            ->filter(fn ($f) => $f->getExtension() === 'sql')
            ->sortByDesc(fn ($f) => $f->getMTime())
            ->values();

        $toDelete = $files->slice($keep);
        foreach ($toDelete as $f) {
            File::delete($f->getPathname());
            $this->line(' • rimosso vecchio backup: ' . $f->getFilename());
        }
    }
}
