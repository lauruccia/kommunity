# DEPLOY CHECKLIST — 30 aprile 2026

Pacchetto modifiche per i 10 punti dell'Analisi 2026 Review.
**Tutto deployabile via cPanel + FileManager + phpMyAdmin (nessun SSH/composer/npm).**

---

## A. Backup pre-deploy (5 minuti)

1. **phpMyAdmin** → seleziona il database Kommunity → tab **Esporta** → metodo **Personalizzato** → spunta tutte le tabelle → formato SQL → **Esegui** (salva il file `.sql` in locale).
2. **FileManager cPanel** → comprimi in zip la cartella `public_html/` (o la root del progetto Laravel) → scarica il backup.
3. Conserva entrambi i file finché tutto è verificato.

---

## B. File da CARICARE / SOSTITUIRE via FileManager

> Per ogni file: in FileManager scegli il file remoto → **Edit** o sovrascrivi con upload.
> Il backup locale dei file precedenti è in `outputs/backup_2026-04-30/` (nel tuo computer).

### Modificati (15 file)

| # | Path remoto |
|---|-------------|
| 1 | `bootstrap/app.php` |
| 2 | `routes/web.php` |
| 3 | `routes/console.php` |
| 4 | `.gitignore` |
| 5 | `app/Models/Page.php` |
| 6 | `app/Http/Controllers/ConversationController.php` |
| 7 | `app/Http/Controllers/ReferralController.php` |
| 8 | `app/Http/Controllers/OneToOneController.php` |
| 9 | `app/Http/Controllers/EventController.php` |
| 10 | `app/Http/Controllers/DirectoryController.php` |
| 11 | `resources/views/dashboard.blade.php` |
| 12 | `resources/views/one-to-ones/index.blade.php` |
| 13 | `resources/views/welcome.blade.php` |
| 14 | `resources/views/layouts/app.blade.php` |
| 15 | `resources/views/layouts/guest.blade.php` |

### Nuovi (8 file)

| # | Path remoto |
|---|-------------|
| 1 | `app/Policies/EventPolicy.php` |
| 2 | `app/Policies/OneToOnePolicy.php` |
| 3 | `app/Policies/ConversationPolicy.php` |
| 4 | `app/Policies/ReferralPolicy.php` |
| 5 | `app/Policies/MemberOnepagePolicy.php` |
| 6 | `app/Console/Commands/BackupDatabase.php` |
| 7 | `resources/views/partials/cookie-banner.blade.php` |
| 8 | `database/sql/2026-04-30_legal_pages.sql` |

> **Crea le cartelle** `app/Policies/` e `resources/views/partials/` se non esistono già.

---

## C. File da ELIMINARE via FileManager (Punto 1 — pulizia repo)

### 17 file `.bak`

```
app/Enums/EventAttendanceStatus.php.bak
app/Http/Controllers/DashboardController.php.bak
app/Http/Controllers/DirectoryController.php.bak
app/Http/Controllers/OnboardingController.php.bak
app/Http/Controllers/ProfileController.php.bak
app/Models/EventRegistration.php.bak
app/Models/MemberProfile.php.bak
app/Notifications/OneToOneReceivedNotification.php.bak
app/Notifications/OneToOneStatusChangedNotification.php.bak
resources/views/conversations/show.blade.php.bak
resources/views/dashboard.blade.php.bak
resources/views/directory/index.blade.php.bak
resources/views/events/show.blade.php.bak
resources/views/layouts/app.blade.php.bak
resources/views/onboarding/_wizard.blade.php.bak
resources/views/profile/edit.blade.php.bak
routes/console.php.bak
```

### 1 archivio

```
vendor.zip   (≈ 18 MB, residuo del primo upload di vendor — non serve più)
```

> In FileManager puoi multi-selezionare con Ctrl+click + tasto **Delete**.

---

## D. AGGIORNAMENTO `.env` produzione (Punto 2 + Punto 5)

Apri `.env` con FileManager → Edit, e verifica che i valori siano questi
(le righe non elencate possono restare invariate):

```ini
APP_NAME=Kommunity
APP_ENV=production
APP_DEBUG=false
APP_URL=https://kommunity.it          # ← URL reale https
APP_LOCALE=it
APP_FALLBACK_LOCALE=it

LOG_CHANNEL=daily
LOG_STACK=daily
LOG_LEVEL=warning                     # ← non "debug"
LOG_DAILY_DAYS=30

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true                  # ← obbligatorio per cifrare cookie sessione
SESSION_SECURE_COOKIE=true            # ← perché HTTPS
SESSION_PATH=/

CACHE_STORE=database
QUEUE_CONNECTION=database

# ── SMTP (Punto 5) ────────────────────────────────────────────────────────────
MAIL_MAILER=smtp
MAIL_HOST=mail.kommunity.it
MAIL_PORT=465
MAIL_USERNAME=info@kommunity.it
MAIL_PASSWORD="Udzwd?_OR@[!6UoO"
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@kommunity.it
MAIL_FROM_NAME="Kommunity"
```

> ⚠️ **Sicurezza**: la password SMTP è transitata in chat. Dopo il deploy,
> rigenerala in cPanel (Email Accounts → info@kommunity.it → Manage →
> Change Password) e aggiorna `MAIL_PASSWORD` nel `.env`.

---

## E. SQL da eseguire in phpMyAdmin (Punto 6)

1. Apri phpMyAdmin → seleziona il database Kommunity → tab **SQL**.
2. Copia/incolla l'intero contenuto di `database/sql/2026-04-30_legal_pages.sql`
   (che hai appena caricato).
3. Premi **Esegui**.

Risultato atteso:

- `pages.privacy` → footer position 1
- `pages.termini` → footer position 2
- `pages.cookie-policy` → footer position 3

I testi sono **placeholder professionali**. Personalizzali subito da
**`/admin` → Filament → Pages** sostituendo:

- `{NOME_AZIENDA}`, `{INDIRIZZO_LEGALE}`, `{P_IVA}`
- `{EMAIL_PRIVACY}` (es. `privacy@kommunity.it`)
- `{FORO_COMPETENTE}`

> Il sanitize HTML del Punto 8 si attiva automaticamente al salvataggio:
> qualunque `<script>` o `onclick=` viene rimosso prima del DB.

---

## F. CRON cPanel (Punto 10)

cPanel → **Cron Jobs** → aggiungi questi due job. Sostituisci `USER` con
il tuo username cPanel (lo vedi nel path `/home/USER/...`).

### F.1 — Scheduler Laravel (1 job copre TUTTO: backup, queue, reminder)

```
* * * * * cd /home/USER/public_html && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

> Questa è la modalità ufficiale Laravel 12. Lo scheduler fa partire
> automaticamente `app:db-backup` alle 03:15 (mantiene 7 backup),
> `queue:work` ogni minuto e `kommunity:send-event-reminders` ogni ora.

### F.2 — (Opzionale) backup giornaliero ridondante

Se preferisci NON dipendere da `schedule:run`, in alternativa:

```
15 3 * * * cd /home/USER/public_html && /usr/local/bin/php artisan app:db-backup --keep=7 >> storage/logs/backup.log 2>&1
* * * * * cd /home/USER/public_html && /usr/local/bin/php artisan queue:work --stop-when-empty --max-time=55 --tries=3 >> /dev/null 2>&1
```

> Verifica il path `php` con il tuo provider (potrebbe essere
> `/usr/local/bin/ea-php82` su EasyApache: chiedi al supporto cPanel se non
> sei sicura).

I backup finiscono in `storage/app/backups/`. **Crea anche una regola
giornaliera in cPanel** (Backup Wizard) come secondo livello di sicurezza.

---

## G. CACHE Laravel (post-upload)

Dopo aver caricato i file e modificato `.env`, vai su:

```
https://kommunity.it/admin/cache
```

(ora protetta dal middleware `role:` — solo super-admin/admin-community/leader-capitolo)
e clicca **Pulisci tutte le cache**. Questo ricompila view, config e route
con i nuovi file.

---

## H. RIEPILOGO MODIFICHE PER PUNTO

| # | Punto | Stato | Cosa cambia |
|---|-------|-------|-------------|
| 1 | Pulizia repo | ✅ Codice | `.gitignore` aggiornato (aggiunti `*.bak`, `vendor.zip`, `storage/app/backups`). 17 file `.bak` + `vendor.zip` da eliminare via FileManager (sezione C). |
| 2 | `.env` produzione | ✅ Istruzioni | Vedi sezione D. Tu modifichi `.env` su cPanel (no commit). |
| 3 | "Pianeta Roma" hardcoded | ✅ Codice | Sostituito in `dashboard.blade.php` (9 occorrenze) e `one-to-ones/index.blade.php` (1) con `{{ $user->memberProfile?->chapter?->name ?? 'Kommunity' }}`. Se l'utente ha un capitolo vede il suo, altrimenti "Kommunity". |
| 4 | Middleware `role:` su admin/cache | ✅ Codice | `bootstrap/app.php`: registrate alias Spatie (`role`, `permission`, `role_or_permission`). `routes/web.php`: gruppo admin/cache ora protetto da `role:super-admin\|admin-community\|leader-capitolo`. Il controller manteneva già `authorizeAdmin()` — ora è defense-in-depth. |
| 5 | Notifiche email | ✅ Codice già presente | Le 5 notifiche **esistevano già** e sono cablate: `MemberWelcomeNotification` (in `Filament\Resources\Users\Pages\CreateUser`), `OneToOneReceivedNotification` + `OneToOneStatusChangedNotification` (in `OneToOneController`), `NewMessageNotification` (in `ConversationController`), `ReferralReceivedNotification` (in `ReferralController`), `SubscriptionApprovedNotification` (in `Filament\Resources\MemberSubscriptions\MemberSubscriptionResource`). **L'invio reale parte non appena**: (a) SMTP corretto in `.env` (sezione D), (b) queue worker attivo (sezione F). |
| 6 | Cookie banner + pagine legali | ✅ Codice + SQL | Nuovo partial `partials/cookie-banner.blade.php` (vanilla JS, zero dipendenze, cookie `km_cookie_consent` 1 anno). Incluso in `app.blade.php`, `guest.blade.php`, `welcome.blade.php`. SQL `2026-04-30_legal_pages.sql` crea/aggiorna `privacy`, `termini`, `cookie-policy` nel CMS. |
| 7 | 5 Policy Laravel | ✅ Codice | Nuovi `EventPolicy`, `OneToOnePolicy`, `ConversationPolicy`, `ReferralPolicy`, `MemberOnepagePolicy`. **Auto-discovery Laravel 12** — nessuna registrazione manuale. Migrati 8 `abort_unless(...,403)` su `$this->authorize(...)` (ConversationController × 2, ReferralController × 1, OneToOneController × 2, EventController × 3). I rimanenti `abort_unless` sono **404** (risorsa non trovata) o **422** (regole business: stato non confermabile, ecc.) e correttamente NON sono autorizzazioni. |
| 8 | Sanitize HTML in `Page` CMS | ✅ Codice | `Page::content` ha ora un mutator `Attribute::set` che pulisce l'HTML al salvataggio. Whitelist conservativa di tag/attributi via `DOMDocument` PHP nativo. Rimuove `<script>`, handler `on*`, `<iframe>`, URL `javascript:`/`data:`/`vbscript:`. Forza `rel="noopener noreferrer"` su link `target="_blank"`. **Zero dipendenze nuove**. |
| 9 | `inRandomOrder` con shuffle cachato | ✅ Codice | `DirectoryController` non usa più `ORDER BY RAND()`. Ora: ID profili attivi vengono mescolati una volta e cachati per 60 minuti (`Cache::remember('directory.random_ids.v1', ...)`); l'ordinamento avviene con `FIELD(id, csv)` — usa l'indice primario. Bonus: **paginazione coerente** (l'utente non perde l'ordine cambiando pagina). |
| 10 | Backup DB + queue worker | ✅ Codice + Cron | Nuovo comando `app:db-backup` (`app/Console/Commands/BackupDatabase.php`), PHP-only via PDO (no `mysqldump`, compatibile shared hosting). Schedulato `dailyAt('03:15')` con retention 7 giorni in `storage/app/backups/`. Aggiunto in `routes/console.php` anche schedule per `queue:work --stop-when-empty`. Cron cPanel in sezione F. |

---

## I. TEST POST-DEPLOY (15 minuti)

In ordine:

1. ✅ **Login** funziona, `/dashboard` non mostra più "Pianeta Roma".
2. ✅ Vai sul tuo profilo → onepage di un membro: nessun 500. Verifica
   che la directory si carichi velocemente (test su `/directory`).
3. ✅ Apri in **incognito** la home `/`: deve apparire il banner cookie.
   Clicca "Accetta tutto", ricarica: il banner sparisce.
4. ✅ Visita `/pagina/privacy`, `/pagina/termini`, `/pagina/cookie-policy`:
   le 3 pagine devono caricare con i testi placeholder.
5. ✅ Vai su `/admin/cache` con un account NON-admin: deve dare 403.
   Riprova con un account super-admin: deve funzionare.
6. ✅ Apri Filament `/admin` → Pages → modifica una pagina, incolla
   `<script>alert(1)</script><p>Testo ok</p>` nel content e salva.
   Riapri la pagina nel CMS pubblico: lo script deve **scomparire**, il `<p>` deve restare.
7. ✅ Crea una richiesta 1:1 verso un altro utente test: l'altro utente
   deve ricevere l'email entro ~1 minuto (se cron F è attivo).
8. ✅ Aspetta fino al giorno dopo le 03:15 → controlla
   `storage/app/backups/` da FileManager: deve esserci un nuovo `.sql`
   con la data corrente.
9. ✅ Manda un referral verso un membro idoneo: cambia status come sender,
   poi come recipient → entrambi devono funzionare; un utente terzo
   estraneo che provasse l'URL deve ricevere 403 (policy attiva).

---

## J. ROLLBACK (in caso di problemi)

Tutti i file pre-modifica sono salvati nel tuo computer in
`outputs/backup_2026-04-30/` (lato Cowork). Per rollback:

1. FileManager → ricarica i file dal backup (sezione A).
2. phpMyAdmin → Importa il backup `.sql` salvato in sezione A.
3. Cancella i 3 record in `pages` (`privacy`, `termini`, `cookie-policy`)
   se vuoi rimuovere anche le pagine legali.

---

## K. NOTE TECNICHE

- **Niente composer required**: Spatie/Permission e Filament sono già
  installati nel `vendor/` esistente. Le 5 nuove policy usano solo classi
  built-in Laravel. Il sanitize HTML usa `DOMDocument` PHP nativo. Il
  backup usa solo PDO/Schema. **Non serve aggiornare `vendor/`**.
- **Auto-discovery policy**: Laravel 12 risolve automaticamente
  `App\Policies\XxxPolicy` per `App\Models\Xxx`. Nessuna registrazione
  manuale in `AuthServiceProvider`.
- **Cache**: dopo l'upload, esegui sempre la pulizia cache (sezione G)
  o eliminami manualmente `bootstrap/cache/*.php` e
  `storage/framework/views/*.php`.

---

## L. CHANGELOG SINTETICO

```
+ 5 nuove Policy Laravel (Event, OneToOne, Conversation, Referral, MemberOnepage)
+ 1 nuovo Artisan command (app:db-backup)
+ 1 nuovo Blade partial (cookie-banner)
+ 1 nuovo SQL seed (3 pagine legali)
+ Spatie role/permission middleware aliases registrati
~ DirectoryController: rimosso ORDER BY RAND(), aggiunto shuffle cachato 1h
~ Page model: HTML sanitize via DOMDocument
~ ConversationController: 2 abort_unless → authorize()
~ ReferralController: 1 abort_unless → authorize()
~ OneToOneController: 2 abort_unless → authorize()
~ EventController: 3 abort_unless → authorize()
~ routes/web.php: middleware role: su /admin/cache/*
~ routes/console.php: scheduler db-backup + queue:work
~ Dashboard + 1:1 + welcome: rimossi 11 hardcoded "Pianeta Roma"
~ layouts/app + guest + welcome: include cookie-banner
~ .gitignore: aggiunti *.bak, vendor.zip, storage/app/backups
```

Generato: 30 aprile 2026 — Laura (Kommunity).
