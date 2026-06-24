# Kommunity — Audit pre-lancio

**Data:** 19 giugno 2026
**Stack:** Laravel 12 · PHP 8.2 · Filament 4 · Spatie Permission · Breeze · Blade/Tailwind/Alpine · MySQL · hosting cPanel condiviso

Valutazione complessiva: **il progetto è solido e quasi pronto.** Autenticazione, autorizzazioni, validazione upload, throttling e config di produzione sono ben impostati. Restano però alcuni punti da chiudere **prima** del go-live, uno dei quali critico.

---

## 1. BLOCKER — da risolvere prima del lancio

### 1.1 Backdoor admin con password nota — CRITICO
`create_admin.php` (root del repo, committato) crea/aggiorna un super-admin con credenziali fisse:

```
admin@kommunity.test / password
```

Lo script è idempotente: se eseguito in produzione, **reimposta** quell'account a `password`. Anche senza eseguirlo, se l'account esiste sul DB di produzione è un accesso amministrativo completo con password banale.

**Azioni:**
- Eliminare l'account `admin@kommunity.test` dal DB di produzione (o cambiare email + password forte) via phpMyAdmin.
- Rimuovere `create_admin.php` dal repo: `git rm create_admin.php`. Sostituire la logica con un Seeder che legge la password da `.env`, mai hardcoded.
- Verificare che nessun altro account abbia password di test.

### 1.2 File `.bak` e script temporanei committati
Sono tracciati in git (nonostante `.gitignore` abbia `*.bak`, sono stati aggiunti prima della regola):

```
app/Http/Controllers/DirectoryController.php.bak2
app/Http/Controllers/MemberOnepageController.php.bak2 / .bak3
app/Http/Controllers/ProfileController.php.bak.troncato
app/Models/User.php.bak3
bootstrap/app.php.bak2
resources/views/.../*.bak2 / .bak3 (vari)
```

Espongono logica vecchia e creano confusione. Rimuoverli:
```bash
git rm $(git ls-files '*.bak*')
git commit -m "chore: rimuove file .bak dal repo"
```
(I `.bak` locali creati come backup di sicurezza vanno bene, ma non vanno committati.)

### 1.3 Verifiche di configurazione produzione
Il `.env.production.example` è corretto (debug false, log warning, session encrypt/secure true). Prima del lancio confermare sul `.env` **reale** di produzione:
- `APP_ENV=production`, `APP_DEBUG=false`
- `APP_URL=https://...` con HTTPS
- `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`
- `LOG_LEVEL=warning` (non `debug`)
- `MAIL_MAILER=smtp` reale (in locale è `log`)
- Eseguire: `php artisan config:cache && route:cache && view:cache` in locale prima del deploy.

---

## 2. IMPORTANTE — da sistemare a ridosso del lancio

### 2.1 Header di sicurezza: manca CSP
`public/.htaccess` imposta già `X-Frame-Options`, `X-Content-Type-Options: nosniff` e `HSTS`. Mancano:
- **Content-Security-Policy** (anche solo in modalità report-only all'inizio) — la difesa più efficace contro XSS.
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy` (disabilitare camera/microfono/geolocation se non usati).

Aggiungere HSTS solo dopo aver confermato che tutto il sito è HTTPS (già condizionato a `env=HTTPS`, ok).

### 2.2 Output non-escaped in Blade
Tre punti usano `{!! !!}`:
- `forum/show.blade.php` → usa `nl2br(e($post->content))` → **sicuro** (escape prima).
- `welcome.blade.php` → SVG hardcoded → sicuro.
- `page.blade.php` e `auth/register.blade.php` → stampano contenuto CMS (`$page->content`, `$body`) **senza escape**.

Rischio: XSS memorizzato se un ruolo non-fidato può editare pagine CMS o i testi della registrazione. Verificare che **solo super-admin/admin** possano modificare quei contenuti (policy su `Page` / SiteSetting). Se sì, rischio accettabile; in alternativa passare i contenuti attraverso un sanitizer HTML (es. HTMLPurifier) prima del salvataggio.

### 2.3 Validazione URL nei campi profilo
In `ProfileUpdateRequest` i campi `website`, `linkedin_url`, `facebook_url`, `instagram_url` sono validati solo come `string|max:255`. Vengono poi normalizzati nel controller, ma un valore tipo `javascript:...` potrebbe finire in un attributo `href`. Aggiungere regola `url` o un controllo schema `http/https`, e in Blade assicurarsi che i link profilo usino solo URL validati.

### 2.4 Copertura test
14 file di test (Auth, Profilo, Eventi, Banner, Onepage). Buona base ma scoperti i flussi più sensibili: **inviti/registrazione, permessi/ruoli, autorizzazioni conversazioni e chat Pianeta, referral**. Aggiungere almeno gli happy-path + un test di accesso negato (403/404) per ogni risorsa con dati altrui (anti-IDOR).

---

## 3. OTTIMIZZAZIONI E CONSIGLI

### 3.1 Performance
- **Cache/sessioni/queue su `database`**: funziona su hosting condiviso, ma sotto carico è il collo di bottiglia. Se l'host offre Redis (nel `.env` locale è già predisposto), valutare `CACHE_STORE=redis` e `SESSION_DRIVER=redis`.
- **Queue**: 20 notifiche già `ShouldQueue` (ottimo). Su cPanel senza worker persistente serve un **cron** che chiami `queue:work --stop-when-empty` ogni minuto, altrimenti le notifiche/email restano in coda. Verificare che il cron esista.
- **MediaController** serve i file via PHP con catena di 5 fallback di path: comodo ma lento e con I/O ad ogni richiesta. Per immagini ad alto traffico, servirle direttamente da `public_html/media/` (URL statico) bypassando PHP; tenere il controller solo come fallback.
- Eager loading già presente in Directory/Dashboard (bene). Conviene un check con Laravel Debugbar/`DB::listen` in locale per stanare eventuali N+1 residui nelle viste forum/eventi.
- Confermare indici DB sui foreign key più interrogati (`chapter_members.chapter_id+status`, `messages.conversation_id`, `planet_chat_messages.chapter_id`). C'è già una migration `performance_indexes` — verificare che sia stata applicata in produzione.

### 3.2 Manutenibilità
- Root del repo affollata di documenti (`ANALISI_*.md`, `DEPLOY_CHECKLIST_*`, `.docx`, vari `.sql`). Spostare in `docs/` e `database/manual_sql/` per pulizia.
- `EventRegistration.php.bak`, `MemberProfile.php.bak`, `Referral.php.bak`, `ReferralPolicy.php.bak` dentro `app/` → rimuovere (vedi 1.2).
- `vendor.zip` (18 MB) committato: rimuoverlo dal repo, è rigenerabile.

### 3.3 Operatività / hardening
- **Backup automatici** DB: impostare un dump giornaliero (cron cPanel) prima di affidarsi solo ai backup dell'host.
- **Rate limiting**: login già protetto; bene il throttle sulle route di scrittura. Valutare un throttle anche su `/newsletter` e sugli endpoint pubblici `/member/{slug}` per evitare scraping.
- **Email verification** attiva (`verified` middleware) — confermare che l'SMTP di produzione consegni davvero (test reale prima del lancio).
- **Pagina `/up` (health check)** esposta: ok, ma assicurarsi che non riveli dettagli.
- Monitorare `storage/logs` con `LOG_CHANNEL=daily` + `LOG_DAILY_DAYS=30` (già nel template prod).

---

## Riepilogo priorità

| Priorità | Voce | Azione |
|---|---|---|
| 🔴 Critico | Backdoor `admin@kommunity.test/password` | Eliminare account + rimuovere `create_admin.php` |
| 🔴 Critico | File `.bak` / `vendor.zip` in git | `git rm` |
| 🔴 Critico | Config `.env` produzione | Verificare debug/https/secure cookie/mail |
| 🟠 Importante | Manca CSP + Referrer/Permissions-Policy | Aggiungere header |
| 🟠 Importante | `{!! !!}` su contenuti CMS | Limitare edit a admin o sanitizzare |
| 🟠 Importante | Campi URL profilo non validati come `url` | Aggiungere regola `url` |
| 🟠 Importante | Test sui flussi sensibili | Aggiungere test autorizzazione/IDOR |
| 🟡 Consigliato | Redis per cache/sessioni | Se disponibile sull'host |
| 🟡 Consigliato | Cron `queue:work` + backup DB | Configurare su cPanel |
| 🟡 Consigliato | MediaController via PHP | Servire statico ad alto traffico |
| 🟡 Consigliato | Pulizia root repo | Spostare doc/sql, rimuovere `.bak` |
