-- Kommunity banner delivery controls update for phpMyAdmin
-- Execute once on database: kommunity_db

ALTER TABLE `banner_placements`
  ADD COLUMN `desktop_width` int unsigned NULL AFTER `section`,
  ADD COLUMN `desktop_height` int unsigned NULL AFTER `desktop_width`,
  ADD COLUMN `mobile_width` int unsigned NULL AFTER `desktop_height`,
  ADD COLUMN `mobile_height` int unsigned NULL AFTER `mobile_width`,
  ADD COLUMN `max_file_size_kb` int unsigned NOT NULL DEFAULT 300 AFTER `mobile_height`,
  ADD COLUMN `allowed_formats` json NULL AFTER `max_file_size_kb`,
  ADD COLUMN `mobile_required` tinyint(1) NOT NULL DEFAULT 0 AFTER `allowed_formats`;

ALTER TABLE `banner_campaigns`
  ADD COLUMN `weight` int unsigned NOT NULL DEFAULT 1 AFTER `priority`;

UPDATE `banner_placements`
SET
  `desktop_width` = 1200,
  `desktop_height` = 250,
  `mobile_width` = 640,
  `mobile_height` = 320,
  `max_file_size_kb` = 300,
  `allowed_formats` = JSON_ARRAY('jpg', 'jpeg', 'png', 'webp'),
  `mobile_required` = 0,
  `updated_at` = NOW()
WHERE `key` = 'directory_top';

UPDATE `banner_placements`
SET
  `desktop_width` = 600,
  `desktop_height` = 600,
  `mobile_width` = 600,
  `mobile_height` = 600,
  `max_file_size_kb` = 250,
  `allowed_formats` = JSON_ARRAY('jpg', 'jpeg', 'png', 'webp'),
  `mobile_required` = 0,
  `updated_at` = NOW()
WHERE `key` = 'directory_sidebar';

INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES
  ('2026_05_18_000004_add_banner_delivery_controls', 999);
