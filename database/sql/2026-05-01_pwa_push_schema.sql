-- ─────────────────────────────────────────────────────────────────────────────
-- KOMMUNITY — Fase 3 PWA + Push (push_subscriptions)
-- DA ESEGUIRE IN phpMyAdmin → SQL → Esegui (UNA VOLTA).
-- Idempotente: se la tabella esiste già, viene saltata.
-- ─────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `push_subscriptions` (
    `id`             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`        BIGINT UNSIGNED NOT NULL,
    `endpoint`       TEXT NOT NULL,
    `endpoint_hash`  VARCHAR(64) NOT NULL,
    `p256dh_key`     VARCHAR(200) NOT NULL,
    `auth_key`       VARCHAR(64) NOT NULL,
    `user_agent`     VARCHAR(500) NULL,
    `last_used_at`   TIMESTAMP NULL,
    `revoked_at`     TIMESTAMP NULL,
    `failure_count`  INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`     TIMESTAMP NULL,
    `updated_at`     TIMESTAMP NULL,
    UNIQUE KEY `push_subs_endpoint_hash_uq` (`endpoint_hash`),
    KEY `push_subs_user_active_idx` (`user_id`, `revoked_at`),
    CONSTRAINT `push_subs_user_id_fkey`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
