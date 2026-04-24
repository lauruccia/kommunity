-- =====================================================================
-- SQL DA ESEGUIRE SU cPanel → phpMyAdmin
-- Esegui questo script se php artisan migrate non è disponibile.
-- Eseguire in ordine dall'alto verso il basso.
-- =====================================================================

-- ── 1. Colonna locale sulla tabella users ────────────────────────────
-- (Migrazione: 2026_04_24_000021_add_locale_to_users_table)
ALTER TABLE `users`
    ADD COLUMN `locale` VARCHAR(5) NOT NULL DEFAULT 'it' AFTER `email`;

-- ── 2. Tabella subscription_plans ────────────────────────────────────
-- (Migrazione: 2026_04_24_000020 - prima parte)
CREATE TABLE IF NOT EXISTS `subscription_plans` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`          VARCHAR(255) NOT NULL,
    `slug`          VARCHAR(255) NOT NULL,
    `description`   TEXT NULL,
    `plan_type`     VARCHAR(255) NOT NULL DEFAULT 'directory_only',
    `price_monthly` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `price_yearly`  DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `trial_days`    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `features`      JSON NULL,
    `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order`    INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`    TIMESTAMP NULL,
    `updated_at`    TIMESTAMP NULL,
    UNIQUE KEY `subscription_plans_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 3. Tabella member_subscriptions ──────────────────────────────────
-- (Migrazione: 2026_04_24_000020 - seconda parte)
CREATE TABLE IF NOT EXISTS `member_subscriptions` (
    `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id`            BIGINT UNSIGNED NOT NULL,
    `plan_id`            BIGINT UNSIGNED NOT NULL,
    `status`             VARCHAR(255) NOT NULL DEFAULT 'pending',
    `payment_method`     VARCHAR(255) NULL,
    `payment_reference`  VARCHAR(255) NULL,
    `payment_notes`      TEXT NULL,
    `requested_at`       TIMESTAMP NULL,
    `trial_ends_at`      TIMESTAMP NULL,
    `starts_at`          TIMESTAMP NULL,
    `ends_at`            TIMESTAMP NULL,
    `approved_by`        BIGINT UNSIGNED NULL,
    `approved_at`        TIMESTAMP NULL,
    `admin_notes`        TEXT NULL,
    `created_at`         TIMESTAMP NULL,
    `updated_at`         TIMESTAMP NULL,
    CONSTRAINT `ms_user_fk`     FOREIGN KEY (`user_id`)     REFERENCES `users`(`id`)                ON DELETE CASCADE,
    CONSTRAINT `ms_plan_fk`     FOREIGN KEY (`plan_id`)     REFERENCES `subscription_plans`(`id`)   ON DELETE RESTRICT,
    CONSTRAINT `ms_approver_fk` FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`)                ON DELETE SET NULL,
    INDEX `ms_user_status` (`user_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 4. Aggiorna la tabella migrations ────────────────────────────────
-- (Segnala a Laravel che le migrazioni sono state eseguite)
INSERT IGNORE INTO `migrations` (`migration`, `batch`)
VALUES
    ('2026_04_24_000020_create_subscription_plans_and_subscriptions', 99),
    ('2026_04_24_000021_add_locale_to_users_table', 99);

-- =====================================================================
-- FINE — Dopo aver eseguito questo script:
--   1. In cPanel → artisan (o via cron "una volta"):
--      php artisan optimize:clear && php artisan optimize
--   2. Oppure svuota manualmente storage/framework/cache/data/
-- =====================================================================
