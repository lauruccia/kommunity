-- ============================================================
-- FIX #1 — Crea tabella one_to_one_references
-- Eseguire in phpMyAdmin prima di andare online.
-- ============================================================

CREATE TABLE `one_to_one_references` (
    `id`                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `one_to_one_request_id`   BIGINT UNSIGNED NOT NULL,
    `author_id`               BIGINT UNSIGNED NOT NULL,
    `recipient_id`            BIGINT UNSIGNED NOT NULL,
    `content`                 TEXT            NULL,
    `rating`                  TINYINT UNSIGNED NULL,
    `tags`                    JSON            NULL,
    `is_recommended`          TINYINT(1)      NULL,
    `created_at`              TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`              TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    -- updateOrCreate() cerca per (one_to_one_request_id, author_id): deve essere UNIQUE
    UNIQUE KEY `oto_ref_request_author_unique` (`one_to_one_request_id`, `author_id`),
    KEY `oto_ref_author_id_idx`    (`author_id`),
    KEY `oto_ref_recipient_id_idx` (`recipient_id`),
    CONSTRAINT `oto_ref_request_fk`
        FOREIGN KEY (`one_to_one_request_id`)
        REFERENCES `one_to_one_requests` (`id`) ON DELETE CASCADE,
    CONSTRAINT `oto_ref_author_fk`
        FOREIGN KEY (`author_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `oto_ref_recipient_fk`
        FOREIGN KEY (`recipient_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
