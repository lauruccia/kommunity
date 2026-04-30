# PATCH — Notifiche async via ShouldQueue (30 aprile 2026 sera)

**Obiettivo**: rendere asincrone le 8 notification che ancora bloccavano il request HTTP. Errori SMTP non causano più 500 utente.

---

## Cosa cambia

Tutte le 11 Notification ora implementano `ShouldQueue` + `use Queueable`. Quando triggherate:

1. Laravel salva il job nella tabella `jobs` (1 query DB veloce)
2. Risponde subito al request HTTP
3. Il queue worker (cron ogni minuto) processa la coda
4. Se l'email fallisce → log + retry automatico (max 3 tentativi) → poi `failed_jobs`
5. Niente 500 utente

## File modificati (8)

| # | Path |
|---|------|
| 1 | `app/Notifications/OneToOneReceivedNotification.php` |
| 2 | `app/Notifications/OneToOneStatusChangedNotification.php` |
| 3 | `app/Notifications/NewMessageNotification.php` |
| 4 | `app/Notifications/ReferralReceivedNotification.php` |
| 5 | `app/Notifications/SubscriptionApprovedNotification.php` |
| 6 | `app/Notifications/EventReminderNotification.php` |
| 7 | `app/Notifications/EventInvitationNotification.php` |
| 8 | `app/Notifications/ForumReplyNotification.php` |

Già `ShouldQueue` da prima (non toccati): `MemberWelcomeNotification`, `OneToOneReminderNotification`, `NewMemberConciergeAlertNotification`.

---

## Pre-deploy: verifica infrastruttura queue

### 1. Tabella `jobs` deve esistere in DB

phpMyAdmin → DB → tab **SQL** → esegui:

```sql
SHOW TABLES LIKE 'jobs';
SHOW TABLES LIKE 'failed_jobs';
```

Se entrambe esistono → ok, vai allo step 2.

Se mancano → eseguile:

```sql
CREATE TABLE IF NOT EXISTS `jobs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `queue` VARCHAR(255) NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `attempts` TINYINT UNSIGNED NOT NULL,
    `reserved_at` INT UNSIGNED NULL,
    `available_at` INT UNSIGNED NOT NULL,
    `created_at` INT UNSIGNED NOT NULL,
    INDEX `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `uuid` VARCHAR(255) NOT NULL UNIQUE,
    `connection` TEXT NOT NULL,
    `queue` TEXT NOT NULL,
    `payload` LONGTEXT NOT NULL,
    `exception` LONGTEXT NOT NULL,
    `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `job_batches` (
    `id` VARCHAR(255) NOT NULL PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `total_jobs` INT NOT NULL,
    `pending_jobs` INT NOT NULL,
    `failed_jobs` INT NOT NULL,
    `failed_job_ids` LONGTEXT NOT NULL,
    `options` MEDIUMTEXT NULL,
    `cancelled_at` INT NULL,
    `created_at` INT NOT NULL,
    `finished_at` INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. Verifica `.env`

cPanel → File Manager → `kommunity/.env` → cerca `QUEUE_CONNECTION`. Deve essere:

```
QUEUE_CONNECTION=database
```

Se è `sync` o `null` → cambia a `database` e salva.

### 3. Queue worker attivo

Il cron `* * * * * cd /home/USER/kommunity && /usr/local/bin/php artisan schedule:run` deve essere attivo (l'avevi già configurato in Fase 0). Verifica: cPanel → Cron Jobs → deve esserci. Se sì → stai tranquilla, lo schedule include già `queue:work --stop-when-empty --max-time=55 --tries=3 --everyMinute`.

---

## Deploy (via git al solito modo)

1. Commit + push dal terminale Laragon:

```bash
git add app/Notifications/
git commit -m "fix: notifiche async via ShouldQueue (no più 500 da SMTP fail)"
git push
```

2. cPanel → Git Version Control → **Update from Remote** → **Deploy HEAD Commit**.

3. **Cache clear** obbligatoria: cPanel File Manager → elimina i file in `kommunity/bootstrap/cache/` (specialmente `services.php` se c'è) e `kommunity/storage/framework/views/`. Oppure tutto in un colpo via `https://kommunity.it/admin/cache` → Pulisci.

---

## Test post-deploy

### Test 1 — Invio messaggio non più 500

Loggati e manda un messaggio a un utente (anche con email finta `@kommunity.test` se ne hai ancora). 

**Comportamento atteso**:
- Il messaggio si salva istantaneamente ✅
- La notifica push arriva (se l'utente è subscriberato) entro pochi secondi ✅
- Niente errore 500 in pagina ✅
- Se l'email fallisce, l'errore va silenziosamente in `storage/logs/laravel.log`

### Test 2 — Verifica jobs in coda

phpMyAdmin → tab **SQL**:

```sql
SELECT COUNT(*) AS pending FROM jobs;
SELECT COUNT(*) AS failed  FROM failed_jobs;
SELECT * FROM jobs ORDER BY id DESC LIMIT 5;
```

Subito dopo aver mandato un messaggio dovresti vedere `pending = 1` o di più. Dopo che il cron gira (entro 1 min) dovrebbe tornare a 0 (job processato e rimosso).

Se vedi `failed_jobs` crescere → significa che SMTP non funziona davvero. Apri uno di quei record e leggi l'`exception` per debug:

```sql
SELECT id, queue, exception FROM failed_jobs ORDER BY id DESC LIMIT 1\G
```

### Test 3 — Pulisci coda (opzionale)

Se vuoi svuotare i job già falliti:

```sql
TRUNCATE TABLE failed_jobs;
```

Per eseguire un singolo job manualmente (debug avanzato), via cron one-shot:

```
cd /home/USER/kommunity && /usr/local/bin/php artisan queue:work --once --tries=1
```

---

## Effetti collaterali / cose da sapere

1. **Lieve ritardo email**: 30-60 secondi tra invio messaggio e arrivo email (tempo che il cron ripassi). È normale per stack senza Redis.

2. **Push notification anche async**: ora il push `web_push` parte dopo che il queue worker processa il job. Quindi anche il push può tardare 30-60s. Se vuoi push istantaneo, dovremmo separare il channel `web_push` da `mail` con queue separate (lavoro extra, valuta se serve).

3. **Email fallite**: vanno in `failed_jobs` dopo 3 retry automatici. Pulisci periodicamente la tabella o monitora con un widget Filament.

4. **Backup DB più pesante**: la tabella `jobs` può crescere (in genere torna vuota subito). Il comando `app:db-backup` la include comunque.

---

## Changelog

```
~ OneToOneReceivedNotification: + ShouldQueue + Queueable + toWebPush()
~ OneToOneStatusChangedNotification: + ShouldQueue + Queueable
~ NewMessageNotification: + ShouldQueue + Queueable
~ ReferralReceivedNotification: + ShouldQueue + Queueable
~ SubscriptionApprovedNotification: + ShouldQueue + Queueable
~ EventReminderNotification: + ShouldQueue + Queueable
~ EventInvitationNotification: + ShouldQueue + Queueable
~ ForumReplyNotification: + ShouldQueue + Queueable
```

11 Notification su 11 sono ora asincrone. Il sistema è resiliente a:
- Server SMTP irraggiungibile
- Mailbox destinazione piena
- Email blacklisted
- Domini fake (es. `@kommunity.test`)

Generato: 30 aprile 2026 — Patch SMTP-resilient.
