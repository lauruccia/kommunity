-- ============================================================
-- Indici di performance — Kommunity — 2026-05-22
-- Eseguire su phpMyAdmin oppure via cPanel → MySQL Databases
-- Ogni CREATE INDEX usa IF NOT EXISTS: sicuro da eseguire
-- più volte senza errori.
-- ============================================================

-- ── 1. member_profiles ───────────────────────────────────────
-- active_chapter_id: filtro pianeta usato in quasi ogni query
CREATE INDEX IF NOT EXISTS idx_mp_active_chapter_id
    ON member_profiles (active_chapter_id);

-- Filtro combinato directory (is_active + is_visible_in_directory)
CREATE INDEX IF NOT EXISTS idx_mp_active_visible
    ON member_profiles (is_active, is_visible_in_directory);

-- ── 2. one_to_one_requests ───────────────────────────────────
-- Query: WHERE requester_id = X [AND status = Y]
CREATE INDEX IF NOT EXISTS idx_oto_requester_status
    ON one_to_one_requests (requester_id, status);

-- Query: WHERE recipient_id = X [AND status = Y]
CREATE INDEX IF NOT EXISTS idx_oto_recipient_status
    ON one_to_one_requests (recipient_id, status);

-- ── 3. forum_threads ─────────────────────────────────────────
-- ORDER BY is_pinned DESC, created_at DESC WHERE chapter_id = X
CREATE INDEX IF NOT EXISTS idx_ft_chapter_created
    ON forum_threads (chapter_id, created_at);

-- ── 4. banner_impressions ────────────────────────────────────
-- Aggregazioni report: WHERE banner_campaign_id = X AND shown_at > Y
CREATE INDEX IF NOT EXISTS idx_bi_campaign_shown
    ON banner_impressions (banner_campaign_id, shown_at);

-- ── 5. users ─────────────────────────────────────────────────
-- ORDER BY last_seen_at DESC (presenza online in directory)
CREATE INDEX IF NOT EXISTS idx_users_last_seen_at
    ON users (last_seen_at);

-- ── 6. chapter_members ───────────────────────────────────────
-- JOIN: WHERE user_id = X AND chapter_id = Y AND status = 'active'
-- (esiste già UNIQUE su (chapter_id, user_id) ma non copre query user-first con status)
CREATE INDEX IF NOT EXISTS idx_cm_user_chapter_status
    ON chapter_members (user_id, chapter_id, status);

-- ============================================================
-- Verifica: mostra tutti gli indici aggiunti
-- ============================================================
SELECT
    table_name,
    index_name,
    GROUP_CONCAT(column_name ORDER BY seq_in_index) AS columns,
    non_unique
FROM information_schema.STATISTICS
WHERE table_schema = DATABASE()
  AND index_name IN (
      'idx_mp_active_chapter_id',
      'idx_mp_active_visible',
      'idx_oto_requester_status',
      'idx_oto_recipient_status',
      'idx_ft_chapter_created',
      'idx_bi_campaign_shown',
      'idx_users_last_seen_at',
      'idx_cm_user_chapter_status'
  )
GROUP BY table_name, index_name, non_unique
ORDER BY table_name, index_name;
