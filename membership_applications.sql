-- ═══════════════════════════════════════════════════════════════════════
--  KOMMUNITY — Candidature di ammissione (membership_applications)
--  Da eseguire in phpMyAdmin sul database di PRODUZIONE.
--  Equivalente della migrazione locale:
--    2026_07_09_000001_create_membership_applications.php
-- ═══════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `membership_applications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `source` VARCHAR(10) NOT NULL DEFAULT 'home',
  `presenter_user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `chapter_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `applicant_type` VARCHAR(10) NOT NULL DEFAULT 'privato',
  `vat_number` VARCHAR(20) NULL DEFAULT NULL,
  `profession` VARCHAR(255) NULL DEFAULT NULL,
  `referrer_name` VARCHAR(255) NULL DEFAULT NULL,
  `locale` VARCHAR(5) NOT NULL DEFAULT 'it',
  `status` VARCHAR(10) NOT NULL DEFAULT 'pending',
  `rejection_reason` TEXT NULL DEFAULT NULL,
  `reviewed_by_user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `reviewed_at` TIMESTAMP NULL DEFAULT NULL,
  `created_user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `membership_applications_status_index` (`status`),
  KEY `membership_applications_email_index` (`email`),
  KEY `membership_applications_presenter_user_id_foreign` (`presenter_user_id`),
  KEY `membership_applications_chapter_id_foreign` (`chapter_id`),
  KEY `membership_applications_reviewed_by_user_id_foreign` (`reviewed_by_user_id`),
  KEY `membership_applications_created_user_id_foreign` (`created_user_id`),
  CONSTRAINT `membership_applications_presenter_user_id_foreign`
    FOREIGN KEY (`presenter_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `membership_applications_chapter_id_foreign`
    FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `membership_applications_reviewed_by_user_id_foreign`
    FOREIGN KEY (`reviewed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `membership_applications_created_user_id_foreign`
    FOREIGN KEY (`created_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registra la migrazione così `php artisan migrate` non tenterà di ricrearla
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_07_09_000001_create_membership_applications', COALESCE(MAX(`batch`), 0) + 1
FROM `migrations`
HAVING SUM(`migration` = '2026_07_09_000001_create_membership_applications') = 0;
