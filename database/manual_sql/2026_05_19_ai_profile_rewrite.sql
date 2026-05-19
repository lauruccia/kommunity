-- Aggiunge la colonna use_ai_profile_rewrite alla tabella member_profiles
-- Eseguire una sola volta su cPanel → phpMyAdmin
-- Sicuro: usa IF NOT EXISTS per evitare errori se la colonna esiste già

ALTER TABLE `member_profiles`
    ADD COLUMN IF NOT EXISTS `use_ai_profile_rewrite` TINYINT(1) NOT NULL DEFAULT 0 AFTER `networking_goals`;
