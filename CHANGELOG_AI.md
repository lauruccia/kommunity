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
