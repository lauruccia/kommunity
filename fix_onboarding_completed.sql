-- ============================================================
-- FIX: onboarding_completed per utenti con profilo già completo
-- Esegui su phpMyAdmin → tab SQL
-- Sicuro: usa UPDATE con condizioni precise, non tocca utenti in draft
-- ============================================================

-- Imposta onboarding_completed = 1 per tutti i member_profiles che hanno:
--   - almeno una professione nella pivot table member_profile_profession
--   - una città (city_id NOT NULL)
--   - un telefono (phone NOT NULL e non vuoto)
-- e che hanno ancora onboarding_completed = 0

UPDATE member_profiles mp
INNER JOIN member_profile_profession mpp ON mpp.member_profile_id = mp.id
SET
    mp.onboarding_completed = 1,
    mp.status = CASE
        WHEN mp.status = 'draft' THEN 'pending_approval'
        ELSE mp.status
    END,
    mp.updated_at = NOW()
WHERE
    mp.onboarding_completed = 0
    AND mp.city_id IS NOT NULL
    AND mp.phone IS NOT NULL
    AND mp.phone != '';

-- Verifica quante righe sono state aggiornate (esegui subito dopo):
-- SELECT COUNT(*) FROM member_profiles WHERE onboarding_completed = 1;
