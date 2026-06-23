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
