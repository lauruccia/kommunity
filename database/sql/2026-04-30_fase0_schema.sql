-- ─────────────────────────────────────────────────────────────────────────────
-- KOMMUNITY — Fase 0 (feature flags + reminder + concierge)  v3 (FIX MySQL old)
-- DA ESEGUIRE IN phpMyAdmin → SQL → Esegui (UNA VOLTA, dall'inizio).
--
-- v3: usa INFORMATION_SCHEMA + PREPARE/EXECUTE per idempotenza.
--     Funziona su MariaDB / MySQL di qualsiasi versione (anche vecchie
--     che non supportano "ALTER TABLE ... ADD COLUMN IF NOT EXISTS").
--
-- È sicuro ri-eseguire questo file più volte: i passi già fatti vengono
-- saltati silenziosamente (vedrai delle righe "skip" nel risultato).
-- ─────────────────────────────────────────────────────────────────────────────

-- 1) FEATURE FLAGS (CREATE TABLE IF NOT EXISTS è universale, ok) ─────────────
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


-- ═════════════════════════════════════════════════════════════════════════════
-- 2a) ADD completed_at SU one_to_one_requests (se mancante)
-- ═════════════════════════════════════════════════════════════════════════════
SET @col_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'one_to_one_requests'
      AND COLUMN_NAME  = 'completed_at'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE `one_to_one_requests` ADD COLUMN `completed_at` TIMESTAMP NULL AFTER `recipient_completed_at`',
    'SELECT ''completed_at gia esistente — skip'' AS msg'
);
PREPARE _stmt FROM @sql; EXECUTE _stmt; DEALLOCATE PREPARE _stmt;

-- Backfill: i 1:1 già 'completed' ricevono completed_at = updated_at
UPDATE `one_to_one_requests`
   SET `completed_at` = COALESCE(`updated_at`, CURRENT_TIMESTAMP)
 WHERE `status` = 'completed'
   AND `completed_at` IS NULL;


-- ═════════════════════════════════════════════════════════════════════════════
-- 2b) ADD reminder_24h_sent_at
-- ═════════════════════════════════════════════════════════════════════════════
SET @col_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'one_to_one_requests'
      AND COLUMN_NAME  = 'reminder_24h_sent_at'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE `one_to_one_requests` ADD COLUMN `reminder_24h_sent_at` TIMESTAMP NULL AFTER `completed_at`',
    'SELECT ''reminder_24h_sent_at gia esistente — skip'' AS msg'
);
PREPARE _stmt FROM @sql; EXECUTE _stmt; DEALLOCATE PREPARE _stmt;


-- ═════════════════════════════════════════════════════════════════════════════
-- 2c) ADD reminder_1h_sent_at
-- ═════════════════════════════════════════════════════════════════════════════
SET @col_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'one_to_one_requests'
      AND COLUMN_NAME  = 'reminder_1h_sent_at'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE `one_to_one_requests` ADD COLUMN `reminder_1h_sent_at` TIMESTAMP NULL AFTER `reminder_24h_sent_at`',
    'SELECT ''reminder_1h_sent_at gia esistente — skip'' AS msg'
);
PREPARE _stmt FROM @sql; EXECUTE _stmt; DEALLOCATE PREPARE _stmt;


-- ═════════════════════════════════════════════════════════════════════════════
-- 2d) ADD INDEX su requested_at (perf reminder)
-- ═════════════════════════════════════════════════════════════════════════════
SET @idx_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'one_to_one_requests'
      AND INDEX_NAME   = 'one_to_one_requested_at_idx'
);
SET @sql := IF(@idx_exists = 0,
    'ALTER TABLE `one_to_one_requests` ADD INDEX `one_to_one_requested_at_idx` (`requested_at`)',
    'SELECT ''one_to_one_requested_at_idx gia esistente — skip'' AS msg'
);
PREPARE _stmt FROM @sql; EXECUTE _stmt; DEALLOCATE PREPARE _stmt;


-- ═════════════════════════════════════════════════════════════════════════════
-- 3a) ADD concierge_assigned_at SU users
-- ═════════════════════════════════════════════════════════════════════════════
SET @col_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'users'
      AND COLUMN_NAME  = 'concierge_assigned_at'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE `users` ADD COLUMN `concierge_assigned_at` TIMESTAMP NULL AFTER `email_verified_at`',
    'SELECT ''concierge_assigned_at gia esistente — skip'' AS msg'
);
PREPARE _stmt FROM @sql; EXECUTE _stmt; DEALLOCATE PREPARE _stmt;


-- ═════════════════════════════════════════════════════════════════════════════
-- 3b) ADD concierge_assigned_to SU users
-- ═════════════════════════════════════════════════════════════════════════════
SET @col_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'users'
      AND COLUMN_NAME  = 'concierge_assigned_to'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE `users` ADD COLUMN `concierge_assigned_to` BIGINT UNSIGNED NULL AFTER `concierge_assigned_at`',
    'SELECT ''concierge_assigned_to gia esistente — skip'' AS msg'
);
PREPARE _stmt FROM @sql; EXECUTE _stmt; DEALLOCATE PREPARE _stmt;


-- ═════════════════════════════════════════════════════════════════════════════
-- 3c) ADD concierge_completed_at SU users
-- ═════════════════════════════════════════════════════════════════════════════
SET @col_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'users'
      AND COLUMN_NAME  = 'concierge_completed_at'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE `users` ADD COLUMN `concierge_completed_at` TIMESTAMP NULL AFTER `concierge_assigned_to`',
    'SELECT ''concierge_completed_at gia esistente — skip'' AS msg'
);
PREPARE _stmt FROM @sql; EXECUTE _stmt; DEALLOCATE PREPARE _stmt;


-- ═════════════════════════════════════════════════════════════════════════════
-- 3d) ADD concierge_notes SU users
-- ═════════════════════════════════════════════════════════════════════════════
SET @col_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'users'
      AND COLUMN_NAME  = 'concierge_notes'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE `users` ADD COLUMN `concierge_notes` TEXT NULL AFTER `concierge_completed_at`',
    'SELECT ''concierge_notes gia esistente — skip'' AS msg'
);
PREPARE _stmt FROM @sql; EXECUTE _stmt; DEALLOCATE PREPARE _stmt;


-- ═════════════════════════════════════════════════════════════════════════════
-- 3e) ADD FOREIGN KEY users.concierge_assigned_to → users.id
-- ═════════════════════════════════════════════════════════════════════════════
SET @fk_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME       = 'users'
      AND CONSTRAINT_NAME  = 'users_concierge_assigned_to_fkey'
      AND CONSTRAINT_TYPE  = 'FOREIGN KEY'
);
SET @sql := IF(@fk_exists = 0,
    'ALTER TABLE `users` ADD CONSTRAINT `users_concierge_assigned_to_fkey` FOREIGN KEY (`concierge_assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL',
    'SELECT ''users_concierge_assigned_to_fkey gia esistente — skip'' AS msg'
);
PREPARE _stmt FROM @sql; EXECUTE _stmt; DEALLOCATE PREPARE _stmt;


-- ═════════════════════════════════════════════════════════════════════════════
-- 3f) ADD INDEX su concierge_completed_at
-- ═════════════════════════════════════════════════════════════════════════════
SET @idx_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'users'
      AND INDEX_NAME   = 'users_concierge_completed_idx'
);
SET @sql := IF(@idx_exists = 0,
    'ALTER TABLE `users` ADD INDEX `users_concierge_completed_idx` (`concierge_completed_at`)',
    'SELECT ''users_concierge_completed_idx gia esistente — skip'' AS msg'
);
PREPARE _stmt FROM @sql; EXECUTE _stmt; DEALLOCATE PREPARE _stmt;


-- ═════════════════════════════════════════════════════════════════════════════
-- 4) SEED 10 FEATURE FLAGS
-- ═════════════════════════════════════════════════════════════════════════════
INSERT INTO `feature_flags`
    (`key`, `name`, `group`, `description`, `is_enabled`, `display_order`, `created_at`, `updated_at`)
VALUES
    -- Fase 0 (attive subito)
    ('concierge_onboarding',  'Concierge Onboarding',          'engagement',  'Notifica admin per chiamare/seguire i nuovi iscritti entro 24h. Aumenta conversion al primo accesso.', 1, 10,  NOW(), NOW()),
    ('reminders_one_to_one',  'Reminder 1:1 (24h e 1h)',       'reminders',   'Email di promemoria 24 ore e 1 ora prima di ogni 1:1 confermato.', 1, 20, NOW(), NOW()),
    ('analytics_personal',    'Dashboard analytics membro',    'engagement',  'Mostra al membro KPI personali: 1:1 fatti, valore referral generato, ROI sull''abbonamento.', 1, 30, NOW(), NOW()),
    -- Fase 1
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
    `name`          = VALUES(`name`),
    `group`         = VALUES(`group`),
    `description`   = VALUES(`description`),
    `display_order` = VALUES(`display_order`),
    `updated_at`    = NOW();
-- Nota: NON aggiorniamo `is_enabled` su duplicate, così se l'admin
-- modifica un toggle dal pannello, una nuova esecuzione di questo
-- script non lo resetta.

-- ═════════════════════════════════════════════════════════════════════════════
-- FINE — risultato atteso:
--   • feature_flags: 10 righe
--   • one_to_one_requests: 3 nuove colonne (completed_at, reminder_24h, reminder_1h) + indice
--   • users: 4 nuove colonne (concierge_*) + FK + indice
-- ═════════════════════════════════════════════════════════════════════════════
