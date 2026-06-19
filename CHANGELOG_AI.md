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

## 2026-06-19 — One-to-one: fix riprogrammazione, completamento, UI

- File creati: `database/migrations/2026_06_19_000001_add_rescheduled_by_to_one_to_one_requests.php`
- File modificati: `app/Http/Controllers/OneToOneController.php`, `app/Models/OneToOneRequest.php`, `resources/views/one-to-ones/index.blade.php`, `resources/views/components/dashboard/one-to-one-row.blade.php`, `public/css/kommunity.css`, `resources/views/auth/verify-email.blade.php`
- Cosa è cambiato:
  1. Dopo la conferma di completamento (anche di un solo partecipante) spariscono "Proponi nuovo orario", il modale di riprogrammazione e il box "Annulla richiesta" (guard sia lato vista sia lato controller via `completionStarted()`).
  2. Riprogrammazione: aggiunta colonna `rescheduled_by` per tracciare chi propone. Ora a confermare la nuova proposta è la CONTROPARTE; quando conferma, l'incontro passa subito ad "Accettato" senza attendere il proponente. Pulsante "Accetta" mostrato a chi deve rispondere anche in lista e in dashboard (`canRespondTo()`).
  3. CSS: fix hover del pulsante `.km-button-secondary` su tema scuro (era bianco su bianco, illeggibile) — override in `kommunity.css` scoped a `body.km-bg-dark` (nessun build necessario).
  4. Schermata verifica email post-registrazione semplificata.
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
