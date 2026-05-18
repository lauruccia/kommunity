-- Kommunity production DB update for phpMyAdmin
-- Execute on database: kommunity_db

CREATE TABLE IF NOT EXISTS `profile_video_access_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `requester_id` bigint unsigned NOT NULL,
  `recipient_id` bigint unsigned NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `requested_at` timestamp NULL DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `profile_video_access_requests_requester_id_recipient_id_unique` (`requester_id`, `recipient_id`),
  KEY `profile_video_access_requests_recipient_id_status_index` (`recipient_id`, `status`),
  KEY `profile_video_access_requests_requester_id_status_index` (`requester_id`, `status`),
  CONSTRAINT `profile_video_access_requests_requester_id_foreign` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `profile_video_access_requests_recipient_id_foreign` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `event_role_targets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_role_targets_event_id_role_id_unique` (`event_id`, `role_id`),
  KEY `event_role_targets_role_id_foreign` (`role_id`),
  CONSTRAINT `event_role_targets_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_role_targets_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `advertisers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `banner_campaigns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `advertiser_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `sales_package` varchar(255) NOT NULL DEFAULT 'global',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `priority` int unsigned NOT NULL DEFAULT 0,
  `price` decimal(10,2) DEFAULT NULL,
  `max_impressions` int unsigned DEFAULT NULL,
  `max_clicks` int unsigned DEFAULT NULL,
  `target_url` varchar(255) NOT NULL,
  `open_in_new_tab` tinyint(1) NOT NULL DEFAULT 1,
  `target_mode` varchar(255) NOT NULL DEFAULT 'or',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `banner_campaigns_advertiser_id_foreign` (`advertiser_id`),
  CONSTRAINT `banner_campaigns_advertiser_id_foreign` FOREIGN KEY (`advertiser_id`) REFERENCES `advertisers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `banner_creatives` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `banner_campaign_id` bigint unsigned NOT NULL,
  `image_desktop` varchar(255) NOT NULL,
  `image_mobile` varchar(255) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `headline` varchar(255) DEFAULT NULL,
  `placement_size` varchar(255) NOT NULL DEFAULT 'leaderboard',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `banner_creatives_banner_campaign_id_foreign` (`banner_campaign_id`),
  CONSTRAINT `banner_creatives_banner_campaign_id_foreign` FOREIGN KEY (`banner_campaign_id`) REFERENCES `banner_campaigns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `banner_placements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `section` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `banner_placements_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `banner_campaign_placement` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `banner_campaign_id` bigint unsigned NOT NULL,
  `banner_placement_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `banner_campaign_placement_unique` (`banner_campaign_id`, `banner_placement_id`),
  KEY `banner_campaign_placement_banner_placement_id_foreign` (`banner_placement_id`),
  CONSTRAINT `banner_campaign_placement_banner_campaign_id_foreign` FOREIGN KEY (`banner_campaign_id`) REFERENCES `banner_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `banner_campaign_placement_banner_placement_id_foreign` FOREIGN KEY (`banner_placement_id`) REFERENCES `banner_placements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `banner_campaign_chapter` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `banner_campaign_id` bigint unsigned NOT NULL,
  `chapter_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `banner_campaign_chapter_banner_campaign_id_chapter_id_unique` (`banner_campaign_id`, `chapter_id`),
  KEY `banner_campaign_chapter_chapter_id_foreign` (`chapter_id`),
  CONSTRAINT `banner_campaign_chapter_banner_campaign_id_foreign` FOREIGN KEY (`banner_campaign_id`) REFERENCES `banner_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `banner_campaign_chapter_chapter_id_foreign` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `banner_campaign_region` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `banner_campaign_id` bigint unsigned NOT NULL,
  `region_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `banner_campaign_region_banner_campaign_id_region_id_unique` (`banner_campaign_id`, `region_id`),
  KEY `banner_campaign_region_region_id_foreign` (`region_id`),
  CONSTRAINT `banner_campaign_region_banner_campaign_id_foreign` FOREIGN KEY (`banner_campaign_id`) REFERENCES `banner_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `banner_campaign_region_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `banner_campaign_city` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `banner_campaign_id` bigint unsigned NOT NULL,
  `city_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `banner_campaign_city_banner_campaign_id_city_id_unique` (`banner_campaign_id`, `city_id`),
  KEY `banner_campaign_city_city_id_foreign` (`city_id`),
  CONSTRAINT `banner_campaign_city_banner_campaign_id_foreign` FOREIGN KEY (`banner_campaign_id`) REFERENCES `banner_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `banner_campaign_city_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `banner_campaign_profession` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `banner_campaign_id` bigint unsigned NOT NULL,
  `profession_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `banner_campaign_profession_unique` (`banner_campaign_id`, `profession_id`),
  KEY `banner_campaign_profession_profession_id_foreign` (`profession_id`),
  CONSTRAINT `banner_campaign_profession_banner_campaign_id_foreign` FOREIGN KEY (`banner_campaign_id`) REFERENCES `banner_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `banner_campaign_profession_profession_id_foreign` FOREIGN KEY (`profession_id`) REFERENCES `professions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `banner_campaign_category` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `banner_campaign_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `banner_campaign_category_banner_campaign_id_category_id_unique` (`banner_campaign_id`, `category_id`),
  KEY `banner_campaign_category_category_id_foreign` (`category_id`),
  CONSTRAINT `banner_campaign_category_banner_campaign_id_foreign` FOREIGN KEY (`banner_campaign_id`) REFERENCES `banner_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `banner_campaign_category_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `banner_impressions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `banner_campaign_id` bigint unsigned NOT NULL,
  `banner_creative_id` bigint unsigned DEFAULT NULL,
  `placement_key` varchar(255) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `chapter_id` bigint unsigned DEFAULT NULL,
  `shown_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `banner_impressions_shown_at_index` (`shown_at`),
  KEY `banner_impressions_banner_campaign_id_foreign` (`banner_campaign_id`),
  KEY `banner_impressions_banner_creative_id_foreign` (`banner_creative_id`),
  KEY `banner_impressions_user_id_foreign` (`user_id`),
  KEY `banner_impressions_chapter_id_foreign` (`chapter_id`),
  CONSTRAINT `banner_impressions_banner_campaign_id_foreign` FOREIGN KEY (`banner_campaign_id`) REFERENCES `banner_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `banner_impressions_banner_creative_id_foreign` FOREIGN KEY (`banner_creative_id`) REFERENCES `banner_creatives` (`id`) ON DELETE SET NULL,
  CONSTRAINT `banner_impressions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `banner_impressions_chapter_id_foreign` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `banner_clicks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `banner_campaign_id` bigint unsigned NOT NULL,
  `banner_creative_id` bigint unsigned DEFAULT NULL,
  `placement_key` varchar(255) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `chapter_id` bigint unsigned DEFAULT NULL,
  `clicked_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `banner_clicks_clicked_at_index` (`clicked_at`),
  KEY `banner_clicks_banner_campaign_id_foreign` (`banner_campaign_id`),
  KEY `banner_clicks_banner_creative_id_foreign` (`banner_creative_id`),
  KEY `banner_clicks_user_id_foreign` (`user_id`),
  KEY `banner_clicks_chapter_id_foreign` (`chapter_id`),
  CONSTRAINT `banner_clicks_banner_campaign_id_foreign` FOREIGN KEY (`banner_campaign_id`) REFERENCES `banner_campaigns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `banner_clicks_banner_creative_id_foreign` FOREIGN KEY (`banner_creative_id`) REFERENCES `banner_creatives` (`id`) ON DELETE SET NULL,
  CONSTRAINT `banner_clicks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `banner_clicks_chapter_id_foreign` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `banner_placements` (`key`, `label`, `section`, `is_active`, `created_at`, `updated_at`) VALUES
  ('directory_top', 'Directory - fascia alta', 'directory', 1, NOW(), NOW()),
  ('directory_sidebar', 'Directory - sidebar filtri', 'directory', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`),
  `section` = VALUES(`section`),
  `is_active` = VALUES(`is_active`),
  `updated_at` = NOW();

INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES
  ('2026_05_18_000001_create_profile_video_access_requests_table', 999),
  ('2026_05_18_000002_create_event_role_targets_table', 999),
  ('2026_05_18_000003_create_banner_advertising_tables', 999);
