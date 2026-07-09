# Changelog AI — Kommunity

Log delle modifiche effettuate con assistenza AI. Aggiornare ad ogni sessione.

## Formato entry

```
### YYYY-MM-DD — Descrizione breve
- File modificati: ...
- Cosa è cambiato: ...
- Motivazione: ...
- SQL eseguito: sì/no
- Note: ...
```

---

## 2026-07-09 — Candidature di ammissione: form pubblico su card + homepage, approvazione admin

- File NUOVI: `app/Models/MembershipApplication.php`, `app/Http/Controllers/MembershipApplicationController.php`, `app/Filament/Resources/MembershipApplications/MembershipApplicationResource.php` (+ `Pages/ListMembershipApplications.php`), `app/Mail/MembershipApplicationReceivedMail.php`, `app/Mail/MembershipApprovedMail.php`, `app/Mail/MembershipRejectedMail.php`, `app/Notifications/NewMembershipApplicationNotification.php`, `resources/views/emails/membership-{received,approved,rejected}.blade.php`, `resources/views/partials/membership-apply-home.blade.php`, `lang/it/application.php`, `lang/en/application.php`, `database/migrations/2026_07_09_000001_create_membership_applications.php`, `membership_applications.sql` (root, per phpMyAdmin)
- File modificati: `routes/web.php`, `resources/views/card/show.blade.php`, `resources/views/welcome.blade.php`, `lang/it/push.php`, `lang/en/push.php`
- Cosa è cambiato:
  1. **Nuova entità `MembershipApplication`** (tabella `membership_applications`): candidatura di ammissione da visitatori NON registrati. Campi: nome, email, telefono, tipo privato/azienda, P.IVA (obbligatoria per aziende), professione/attività, "chi ti ha fatto conoscere Kommunity" (solo form home), source (card/home), presentatore (card), pianeta proposto, status pending/approved/rejected + dati revisione. Diversa da `ChapterJoinRequest` (che è per utenti già registrati).
  2. **Rotta pubblica** `POST /candidatura` (`membership.apply`, throttle 5/min, honeypot anti-bot `company_website`). Dalla **card** di un membro il pianeta proposto è il pianeta attivo del proprietario (che risulta presentatore); dalla **homepage** il pianeta è **Kosmos** (slug `kosmos`, fallback su nome LIKE). Blocco duplicati: email già utente o candidatura già pending → errore dedicato.
  3. **Card** (`card/show.blade.php`): nuova sezione premium "Accesso su selezione / Entra in Kommunity" visibile SOLO agli ospiti (@guest, `data-noexport` così non finisce nell'immagine salvata), tradotta nelle 6 lingue inline (it/en/fr/es/de/ro), con form a comparsa (nome, email, telefono, privato/azienda, P.IVA, professione). P.IVA required dinamico via JS quando si sceglie "Azienda".
  4. **Homepage** (`welcome.blade.php` + partial `membership-apply-home.blade.php`): sezione #candidatura con copy elitario ("Non cerchiamo iscritti. Scegliamo persone.") + form con campo extra "Chi ti ha fatto conoscere Kommunity?". CTA aggiornate: link nav "Candidati", bottone header, CTA primaria hero e doppio bottone nella sezione CTA finale. Testi in `lang/{it,en}/application.php`.
  5. **Admin Filament** `/admin/membership-applications` (badge count pending, gruppo Kommunity, solo super-admin/admin-community). Azione **Approva**: modale con select "Pianeta di destinazione" precompilato ma modificabile → in transazione crea User (email verificata, password casuale, invited_by = presentatore o referrer), ruolo `membro`, completa il profilo creato da UserObserver (phone, profession_other, active_chapter_id con override admin), iscrive in `chapter_members`, poi invia `MembershipApprovedMail` con link "imposta password" (token reset Laravel; l'email spiega di usare "Password dimenticata" se scaduto). Azione **Rifiuta**: motivo interno opzionale + toggle email di cortesia (con opzione per includere il motivo).
  6. **Email/notifiche**: conferma ricezione al candidato (nella sua lingua it/en via `->locale()`), notifica admin `NewMembershipApplicationNotification` (mail + campanella + push PWA, chiavi `push.membership_application_*`), benvenuto all'approvazione, rifiuto opzionale. Template in stile `chapter-invitation`.
- Motivazione: richiesta utente — permettere la candidatura dall'esterno (card e homepage) con tono "elite/selezione", approvazione admin e assegnazione automatica al pianeta del presentatore (o Kosmos da home) con possibilità per l'admin di cambiare pianeta.
- SQL eseguito: NO in locale (eseguire `php artisan migrate` in locale). **In produzione eseguire `membership_applications.sql` in phpMyAdmin** prima del deploy.
- Note: backup `.bak.20260709` per i 5 file modificati. Nessun asset statico nuovo (CSS inline nelle view) → nessuna modifica a `.cpanel.yml` né rebuild Vite necessario per questa feature.

## 2026-07-08 — Directory: sidebar mostra solo professioni con almeno un membro

- File modificati: `app/Http/Controllers/DirectoryController.php`
- Cosa è cambiato: la query `$professions` ora aggiunge `whereHas('memberProfiles', …)` con gli stessi filtri dell'elenco membri (`is_active`, `is_visible_in_directory` e scope Pianeta attivo via `chapter_members`). La sidebar "Professioni" della directory elenca quindi solo le professioni con almeno un utente registrato visibile nel Pianeta corrente.
- Motivazione: richiesta utente — la sidebar mostrava tutte le professioni attive, anche vuote.
- SQL eseguito: NO.
- Note: backup `DirectoryController.php.bak`. Nessuna stringa nuova (niente lang). Nessun rebuild Vite.

## 2026-07-08 — Fix "Sessione scaduta" (419) dopo login da mobile

- File modificati: `bootstrap/app.php`, `resources/views/layouts/guest.blade.php`, `lang/it/auth.php`, `lang/en/auth.php`
- Cosa è cambiato:
  1. **Bug root**: in Laravel 12 `prepareException()` converte `TokenMismatchException` in `HttpException(419)` PRIMA dei render callback → il callback registrato su `TokenMismatchException` non scattava mai e veniva mostrata la pagina `errors/419.blade.php` come vicolo cieco. Il callback ora è registrato su `HttpException` con check `getStatusCode() === 419`: redirect a `/admin/login` o `route('login')` con warning bilingue (`auth.session_expired`). Se l'utente è già autenticato (doppio submit o cookie "ricordami") il middleware guest lo porta in dashboard.
  2. **Prevenzione su mobile**: in `layouts/guest.blade.php` aggiunto listener `pageshow` — se la pagina di login viene ripristinata dalla back/forward cache (tab riaperta dopo ore, tipico su mobile) il token CSRF è stantio → reload automatico dal server per ottenere un token fresco.
  3. Nuova chiave lang `auth.session_expired` (IT + EN).
- Motivazione: da mobile, dopo il login compariva sempre la schermata "Sessione scaduta" anche se l'accesso era di fatto riuscito (dashboard accessibile).
- SQL eseguito: NO.
- Note: backup `.bak` per tutti e 4 i file. `errors/419.blade.php` resta come fallback (ora quasi irraggiungibile). Il deploy `.cpanel.yml` svuota già le view compilate in `storage/framework/views/`.

## 2026-07-08 — Profilo: limite massimo 3 professioni + fix campo professioni admin (era rotto)

- File modificati: `app/Http/Requests/ProfileUpdateRequest.php`, `app/Http/Controllers/ProfileController.php`, `app/Models/Profession.php`, `app/Filament/Resources/MemberProfiles/MemberProfileResource.php`, `app/Filament/Resources/MemberProfiles/Pages/EditMemberProfile.php`, `app/Filament/Resources/MemberProfiles/Pages/CreateMemberProfile.php`, `resources/views/profile/partials/update-profile-information-form.blade.php`, `resources/views/profile/partials/_multiselect.blade.php`, `lang/it/profile.php`, `lang/en/profile.php`
- Cosa è cambiato:
  1. **Limite lato utente**: `profession_ids` con regola `max:3` + messaggio bilingue (`profile.professions_max_error`). Il componente Alpine `kmMultiSelect` accetta un 4° parametro opzionale `maxSelected` (=3 per "In quale settore lavori"): al limite, le opzioni non selezionate sono disabilitate/attenuate. Hint sotto la label (`profile.professions_max_hint`). Il multi-select "professionisti da conoscere" resta illimitato.
  2. **Fix bug admin Filament**: il select "Professioni (selezione multipla)" in MemberProfileResource era NON funzionante — senza `->relationship()` né sync: non caricava le professioni dal DB e scartava silenziosamente la selezione al salvataggio. Ora: idratazione via `afterStateHydrated` + `dehydrated(false)` + sync manuale in `EditMemberProfile`/`CreateMemberProfile` (pattern admin_planets), con `maxItems(3)`.
  3. **Helper centralizzati** in `Profession`: `expandWithAncestors()` (auto-include padri gerarchici, prima inline in ProfileController) e `stripAncestors()` (rimuove i padri auto-inclusi per la pre-selezione nei form, così il limite 3 vale solo sulle scelte effettive dell'utente — evita il lockout di profili con 3 figlie + padri in DB).
- Motivazione: richiesta utente — prima si potevano selezionare infinite professioni; verifica admin ha rivelato il campo rotto.
- SQL eseguito: NO (nessuna modifica di schema).
- Note: backup `.bak` per tutti i file. In DB restano possibili >3 righe pivot per via dei padri auto-inclusi (comportamento voluto, utile ai filtri directory).

## 2026-07-07 — Sicurezza: blocco esecuzione PHP in media/ + pulizia script legacy (post-attacco)

- File modificati: `public/media/.htaccess`, `.cpanel.yml`
- Contesto: secondo giorno consecutivo di sito giù con file mancanti in `public_html/` (vedi incidente 2026-07-05: `index.php` sparito, probabile quarantena antivirus hosting → sospetta webshell/malware nell'account).
- Cosa è cambiato:
  1. `public/media/.htaccess`: aggiunto blocco esecuzione script (`FilesMatch` deny su .php/.phtml/.phar/ecc. + `RemoveHandler` + `php_flag engine off`). `media/` è l'unica cartella scrivibile dal web (upload utenti): ora qualsiasi script piazzato lì è inerte.
  2. `.cpanel.yml`: rimossa la copia di `migrate-media.php` in deploy; aggiunto `rm -f` dei legacy script (`migrate-media.php`, `deploy_view.php`, `opcache-reset.php`, `deploy_content.txt`) da `public_html/` a ogni deploy.
- SQL eseguito: NO.
- Note: backup `.htaccess.bak` e `.cpanel.yml.bak2`. Azioni server-side raccomandate (manuali): cambio password cPanel/FTP + 2FA, scansione malware hosting, ModSecurity ON, Cloudflare davanti al dominio, verifica cron job e FTP account sconosciuti.

## 2026-06-23 — Profilo: riepilogo errori visibile + log validazione (diagnosi "profilo non salva")

- File modificati: `resources/views/profile/partials/update-profile-information-form.blade.php`, `app/Http/Requests/ProfileUpdateRequest.php`, `lang/it/profile.php`, `lang/en/profile.php`
- Contesto: un cliente riferiva che, dopo aver compilato il profilo 2 volte, la sua pagina di modifica restava **vuota** ("non salva"). Sintomo confermato: al salvataggio la pagina si ricarica senza messaggi e i dati non vengono salvati. Causa: la validazione di `ProfileUpdateRequest` fallisce su un campo **obbligatorio** (`profession_ids` min:1, `city_id`/`region_id`/`province_id`, `phone`) e l'utente viene rimandato indietro **senza alcun errore in evidenza** — il form aveva solo i piccoli `<x-input-error>` accanto ai singoli campi, nessun riepilogo in cima. Quindi l'utente non capiva cosa bloccasse il salvataggio.
- Cosa è cambiato:
  1. **Banner riepilogo errori** in cima al form di modifica profilo: mostra `$errors->all()` quando la validazione fallisce, così il campo bloccante è immediatamente visibile.
  2. **Log di validazione**: override di `failedValidation()` in `ProfileUpdateRequest` → scrive in `storage/logs/laravel.log` `user_id`, `email` e l'elenco dei campi falliti. Permette di identificare in produzione quale campo blocca il singolo cliente.
- Bilingue: nuova chiave `validation_summary_title` aggiunta a `lang/it/profile.php` e `lang/en/profile.php`.
- SQL eseguito: NO (nessuna modifica di schema).
- Note: backup pre-modifica `.bak` per tutti e 4 i file. Nessun rebuild Vite necessario (solo Blade + lang + PHP). Possibile concausa da verificare lato dati: account duplicato/omonimo (vedi entry card sotto) — controllare se il cliente ha più di un profilo.

## 2026-06-23 — Card: irrobustimento risoluzione profilo (anti card vuota)

- File modificati: `app/Http/Controllers/CardController.php`, `resources/views/card/show.blade.php`
- Contesto: la card pubblica `/card/{slug}` mostrava un biglietto **vuoto** (solo nome + iniziali, nessun contatto) per Linda Gean. Causa reale: **account duplicato** — un secondo utente omonimo (`user_id 7`, linda@kommunity.it) senza profilo. Il doppione vuoto è stato eliminato (mantenuto `user_id 10` con profilo completo e card `linda-gean-2`). Per evitare il ripetersi del problema in futuro (duplicati / omonimie / onboarding incompleto), irrobustita la card.
- Cosa è cambiato:
  1. **Risoluzione deterministica del profilo**: nuovo metodo privato `CardController::resolveProfile()` usato sia in `show()` sia in `vcard()`. Non si affida più alla relazione `User::memberProfile()` (hasOne non filtrata, non deterministica con righe multiple): seleziona sempre `MemberProfile` per `user_id` ordinando `is_active DESC, id DESC` → preferisce la riga attiva e più recente, mai una vuota/inattiva.
  2. **Fallback "profilo in allestimento"**: nuovo flag `$hasProfileData` (true solo se il profilo ha avatar/telefono/sito/azienda/città/professione/social/email visibile). Se falso, la view mostra un placeholder pulito e centrato (icona + titolo + testo) invece della card senza dati; per l'utente proprietario loggato compare il bottone "Modifica profilo".
  3. Disattivato l'auto-download della vCard quando `$hasProfileData` è false (niente contatto col solo nome).
- Bilingue: nuove chiavi `incomplete_title`, `incomplete_text` aggiunte a tutte e 6 le lingue dell'array `$translations` in `show.blade.php` (it, en, fr, es, de, ro).
- SQL eseguito: NO (nessuna modifica di schema). Bonifica dati: eliminato `user_id 7` dal pannello admin.
- Note: backup pre-modifica `app/Http/Controllers/CardController.php.bak` e `resources/views/card/show.blade.php.bak.20260623`. Nessun rebuild Vite necessario (CSS inline nella view standalone).

---

## 2026-06-23 — Card: rimosso auto-download vCard, bottone "Salva biglietto da visita"

- File modificati: `resources/views/card/show.blade.php`
- Cosa è cambiato:
  1. **Rimosso del tutto il download automatico della vCard** all'apertura della card (eliminato lo script con iframe + sessionStorage). Ora il `.vcf` si scarica SOLO se il visitatore preme manualmente "Aggiungi ai contatti" (bottone già esistente, invariato).
  2. Il bottone immagine è stato rinominato da "Salva come immagine" a **"Salva biglietto da visita"** in tutte e 6 le lingue (chiave `save_image`): salva il PNG del biglietto nelle immagini/galleria.
- SQL eseguito: NO. Vendor: nessun aggiornamento. Backup: `show.blade.php.bak.<timestamp>`.

## 2026-06-23 — Card: salva come immagine + Aggiungi a Home (PWA offline)

- File creati: `public/card-sw.js`
- File modificati: `app/Http/Controllers/CardController.php`, `routes/web.php`, `resources/views/card/show.blade.php`, `.cpanel.yml`
- Cosa è cambiato:
  1. **Salva come immagine**: nuovo bottone "Salva come immagine" sulla card che genera un PNG del biglietto con html2canvas (caricato da CDN solo al click). Gli elementi interattivi (bottoni, footer) sono marcati `data-noexport` ed esclusi dall'immagine; resta avatar/nome/professione/azienda/contatti/social. Avatar è same-origin → nessun problema CORS.
  2. **Aggiungi a Home (PWA)**: `card-sw.js` è un service worker dedicato con scope `/card/` (non tocca l'app autenticata né il push `sw.js`), che cacha la pagina (network-first) e gli asset (cache-first) → la card è disponibile **offline** dopo la prima apertura. Aggiunto `CardController::manifest()` + route `card.manifest` (`/card/{slug}/manifest.webmanifest`) per installazione standalone su Android. Su iOS i meta `apple-*` + istruzioni "Aggiungi alla schermata Home".
  3. `.cpanel.yml`: aggiunta copia di `public/card-sw.js` in `public_html/`.
- Bilingue: nuove chiavi `save_image`, `add_to_home`, `ios_hint` aggiunte a tutte e 6 le lingue dell'array `$translations` in `show.blade.php` (it, en, fr, es, de, ro).
- SQL eseguito: NO
- Note: nessun aggiornamento `vendor` necessario. Service worker registrabile solo su HTTPS/localhost (in produzione kommunity.it è HTTPS). Backup: `.bak` di controller, routes, view, .cpanel.yml.

## 2026-06-19 — One-to-one: fix riprogrammazione, completamento, UI

- File creati: `database/migrations/2026_06_19_000001_add_rescheduled_by_to_one_to_one_requests.php`
- File modificati: `app/Http/Controllers/OneToOneController.php`, `app/Models/OneToOneRequest.php`, `resources/views/one-to-ones/index.blade.php`, `resources/views/components/dashboard/one-to-one-row.blade.php`, `public/css/kommunity.css`, `resources/views/auth/verify-email.blade.php`
- Cosa è cambiato:
  1. Dopo la conferma di completamento (anche di un solo partecipante) spariscono "Proponi nuovo orario", il modale di riprogrammazione e il box "Annulla richiesta" (guard sia lato vista sia lato controller via `completionStarted()`).
  2. Riprogrammazione: aggiunta colonna `rescheduled_by` per tracciare chi propone. Ora a confermare la nuova proposta è la CONTROPARTE; quando conferma, l'incontro passa subito ad "Accettato" senza attendere il proponente. Pulsante "Accetta" mostrato a chi deve rispondere anche in lista e in dashboard (`canRespondTo()`).
  3. CSS: fix hover del pulsante `.km-button-secondary` su tema scuro (era bianco su bianco, illeggibile) — override in `kommunity.css` scoped a `body.km-bg-dark` (nessun build necessario).
  4. Schermata verifica email post-registrazione semplificata.
  5. Modale "Nuova richiesta": campo "Oppure proponi data e ora" reso sempre visibile (riquadro etichettato) anche quando l'utente ha slot; slot disponibilità mostrati in griglia multi-colonna invece di uno per riga.

## 2026-06-19 — Terminologia: "membro/membri" → "utente/utenti"

- Cosa è cambiato: sostituito il testo visibile "membro/membri" con "utente/utenti" in tutto il sito utente E nel pannello admin (label Filament, notifiche, email, enum, messaggi flash, lang/it). Gestita l'elisione degli articoli (es. "del membro"→"dell'utente", "ai membri"→"agli utenti", "il membro"→"l'utente").
- NON modificati (per non rompere nulla): il ruolo Spatie `'membro'` (assegnato alla registrazione, controllato dalle policy), i seed degli slug/referral (`'membro-'.id`, `?: 'membro'`), le chiavi permessi (`members.*`), gli identificatori interni (es. action key `aggiungi_membro`) e i commenti nel codice.
- File: ~70 file tra `app/Filament`, `app/Notifications`, `app/Http/Controllers`, `app/Models`, `app/Enums`, `app/Observers`, `resources/views/**`, `lang/it/**`.
- Nota tecnica: `resources/views/one-to-ones/index.blade.php` conteneva un byte UTF-8 troncato (in una riga di commento decorativa `─`) che aveva causato un troncamento del file in coda durante un'edit precedente; coda ripristinata dal backup di inizio sessione e file reso UTF-8 valido. Backup: `.bak.*`, `.bak2.*`, `.bak3.*`, `.truncated.*`.
- SQL eseguito: NO
- Bilingue: invariate le stringhe EN (`lang/en/`) che usano già "member/members".
- Bug "completare un 1:1 ne completa un altro": INDAGATO. L'unico codice che scrive `*_completed_at`/`status=completed` è `OneToOneController::updateStatus`, che opera sul singolo record vincolato dalla route `{oneToOneRequest}`. Nessun update massivo / observer / comando schedulato / azione Filament tocca più record. Non riproducibile via codice (probabile dato pregresso o incontri reciproci). Nessuna modifica necessaria.
- Motivazione: richieste utente (Laura) test one-to-one
- SQL eseguito: NO — eseguire manualmente in phpMyAdmin:
  ```sql
  ALTER TABLE `one_to_one_requests`
    ADD COLUMN `rescheduled_by` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `status`,
    ADD CONSTRAINT `one_to_one_requests_rescheduled_by_foreign`
      FOREIGN KEY (`rescheduled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
  ```
- Note: `public/build/` NON rigenerato (fix CSS in kommunity.css non lo richiede). I file `.bak.20260619_094313` sono i backup pre-modifica.

---

## 2026-06-15 — Chat di gruppo Pianeta

- File creati: `database/migrations/2026_06_15_000001_create_planet_chat_messages.php`, `app/Models/PlanetChatMessage.php`, `app/Http/Controllers/PlanetChatController.php`, `resources/views/planet-chat/show.blade.php`, `lang/it/planet_chat.php`, `lang/en/planet_chat.php`
- File modificati: `routes/web.php`, `app/Support/MemberNavigation.php`, `lang/it/nav.php`, `lang/en/nav.php`, `AI_CONTEXT.md`
- Cosa è cambiato: aggiunta chat di gruppo per pianeta (polling 3s + push notification PWA). Ogni pianeta ha la propria chat, accessibile solo ai membri attivi (`chapter_members.status=active`). Voce "Chat Pianeta" aggiunta al menu di navigazione con redirect automatico al pianeta dell'utente.
- Motivazione: richiesta utente per comunicazione di gruppo all'interno del pianeta
- SQL eseguito: NO — eseguire manualmente in phpMyAdmin:
  ```sql
  CREATE TABLE planet_chat_messages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    chapter_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX planet_chat_messages_chapter_id_id_index (chapter_id, id),
    CONSTRAINT fk_pcm_chapter FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE,
    CONSTRAINT fk_pcm_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  ```
- Note: architettura polling pura (no WebSocket) — compatibile con hosting condiviso cPanel. Push notification via WebPushService esistente.

---

## 2026-06-10 — Setup file di contesto AI

- File creati: `AI_CONTEXT.md`, `PROJECT_MAP.md`, `CHANGELOG_AI.md`, `AGENTS.md`
- Cosa è cambiato: aggiunti file di orientamento per sessioni AI future
- Motivazione: facilitare il lavoro AI con contesto persistente sul progetto
- SQL eseguito: no
- Note: nessuna modifica al codice applicativo
