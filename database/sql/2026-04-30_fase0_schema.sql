-- ─────────────────────────────────────────────────────────────────────────────
-- KOMMUNITY — Fase 0 (feature flags + reminder + concierge)
-- DA ESEGUIRE IN phpMyAdmin → SQL → Esegui (UNA SOLA VOLTA).
-- Questo file è equivalente alle 3 migration in database/migrations/.
-- Se preferisci, puoi anche eseguire `php artisan migrate --force` via cron.
-- ─────────────────────────────────────────────────────────────────────────────

-- 1) FEATURE FLAGS ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `feature_flags` (
    `id`            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key`           VARCHAR(64) NOT NULL UNIQUE,
    `name`          VARCHAR(160) NOT NULL,
    `group`         VARCHAR(64) NOT NULL DEFAULT 'general',
    `description`   TEXT NULL,
    `is_enabled`    TINYINT(1) NOT NULL DEFAULT 0,
    `settings`      JSON NULL,
    `display_order` INT UNSIGNED NOT NULL DEFAULT 100,
    `created_at`    TIMESTAMP NULL,
    `updated_at`    TIMESTAMP NULL,
    INDEX `feature_flags_group_enabled_idx` (`group`, `is_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) REMINDER 1:1 ─────────────────────────────────────────────────────────────
ALTER TABLE `one_to_one_requests`
    ADD COLUMN `reminder_24h_sent_at` TIMESTAMP NULL AFTER `completed_at`,
    ADD COLUMN `reminder_1h_sent_at`  TIMESTAMP NULL AFTER `reminder_24h_sent_at`,
    ADD INDEX `one_to_one_requested_at_idx` (`requested_at`);

-- 3) CONCIERGE ONBOARDING ─────────────────────────────────────────────────────
ALTER TABLE `users`
    ADD COLUMN `concierge_assigned_at`   TIMESTAMP NULL AFTER `email_verified_at`,
    ADD COLUMN `concierge_assigned_to`   BIGINT UNSIGNED NULL AFTER `concierge_assigned_at`,
    ADD COLUMN `concierge_completed_at`  TIMESTAMP NULL AFTER `concierge_assigned_to`,
    ADD COLUMN `concierge_notes`         TEXT NULL AFTER `concierge_completed_at`,
    ADD CONSTRAINT `users_concierge_assigned_to_fkey`
        FOREIGN KEY (`concierge_assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    ADD INDEX `users_concierge_completed_idx` (`concierge_completed_at`);

-- 4) SEED FEATURE FLAGS ───────────────────────────────────────────────────────
-- Tutti i flag iniziano DISATTIVATI tranne le 3 feature di Fase 0 (concierge,
-- reminders, analytics_personal). Puoi modificare questi valori dal pannello
-- Filament → "Feature Flags" senza toccare il DB.
INSERT INTO `feature_flags`
    (`key`, `name`, `group`, `description`, `is_enabled`, `display_order`, `created_at`, `updated_at`)
VALUES
    -- Fase 0 (attive subito)
    ('concierge_onboarding',  'Concierge Onboarding',          'engagement',  'Notifica admin per chiamare/seguire i nuovi iscritti entro 24h. Aumenta conversion al primo accesso.', 1, 10,  NOW(), NOW()),
    ('reminders_one_to_one',  'Reminder 1:1 (24h e 1h)',       'reminders',   'Email di promemoria 24 ore e 1 ora prima di ogni 1:1 confermato.', 1, 20, NOW(), NOW()),
    ('analytics_personal',    'Dashboard analytics membro',    'engagement',  'Mostra al membro KPI personali: 1:1 fatti, valore referral generato, ROI sull''abbonamento.', 1, 30, NOW(), NOW()),

    -- Fase 1 (placeholder, attivare quando implementate)
    ('stripe_checkout',       'Stripe Checkout abbonamenti',   'payments',    'Pagamento abbonamenti via Stripe Checkout, con ricevuta PDF + IVA 22%.', 0, 100, NOW(), NOW()),

    -- Fase 2
    ('ai_matching',           'AI Matching membri',            'ai',          'Suggerimenti di membri compatibili usando embeddings OpenAI text-embedding-3-small.', 0, 200, NOW(), NOW()),
    ('calendar_sync',         'Calendar sync (Google/Outlook/.ics)', 'integrations', 'Sincronizzazione 1:1 ed eventi con Google Calendar, Outlook e .ics download.', 0, 210, NOW(), NOW()),
    ('video_embedded',        'Video meeting embedded (Jitsi)','meetings',    'Stanza Jitsi public meet.jit.si auto-generata per ogni 1:1 confermato.', 0, 220, NOW(), NOW()),

    -- Fase 3
    ('pwa_push',              'PWA + push notification',       'mobile',      'App installabile e notifiche push reali (richiede VAPID + iOS HTTPS).', 0, 300, NOW(), NOW()),
    ('gamification',          'Gamification (badge + classifica)', 'engagement', 'Badge "10 incontri", "5 referral chiusi", classifica annuale per capitolo.', 0, 310, NOW(), NOW()),
    ('marketplace',           'Marketplace servizi',           'commerce',    'I membri pubblicano offerte/richieste di servizi (commissione opzionale).', 0, 320, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    `name`        = VALUES(`name`),
    `group`       = VALUES(`group`),
    `description` = VALUES(`description`),
    `display_order` = VALUES(`display_order`),
    `updated_at`  = NOW();
-- Nota: NON aggiorniamo `is_enabled` su duplicate, così se l'admin
-- modifica un toggle dal pannello, una nuova esecuzione di questo
-- script non lo resetta.
