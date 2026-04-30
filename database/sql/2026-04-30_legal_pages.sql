-- ─────────────────────────────────────────────────────────────────────────────
-- Kommunity — Pagine legali GDPR (privacy / termini / cookie policy)
-- Eseguire UNA SOLA VOLTA via phpMyAdmin → SQL.
-- I testi qui sotto sono PLACEHOLDER professionali da personalizzare con il
-- vostro avvocato (sostituire {NOME_AZIENDA}, {INDIRIZZO_LEGALE}, {P_IVA},
-- {EMAIL_PRIVACY}, ecc.).
-- ─────────────────────────────────────────────────────────────────────────────

-- 1) PRIVACY POLICY ───────────────────────────────────────────────────────────
INSERT INTO `pages`
    (`title`, `slug`, `content`, `meta_description`,
     `show_in_nav`, `show_in_footer`, `nav_order`, `footer_order`, `is_published`,
     `created_at`, `updated_at`)
VALUES
    ('Privacy Policy', 'privacy',
     '<h1>Informativa sulla privacy</h1>\n<p>Ultimo aggiornamento: 30/04/2026.</p>\n<h2>1. Titolare del trattamento</h2>\n<p>Il titolare del trattamento dei dati è <strong>{NOME_AZIENDA}</strong>, con sede in {INDIRIZZO_LEGALE} — P.IVA {P_IVA}. Per qualunque richiesta in materia di privacy puoi scrivere a <a href="mailto:{EMAIL_PRIVACY}">{EMAIL_PRIVACY}</a>.</p>\n<h2>2. Dati raccolti</h2>\n<p>Raccogliamo: dati di registrazione (nome, email), dati di profilo professionale che decidi di condividere (azienda, biografia, professione, capitolo), dati di interazione con la piattaforma (richieste 1:1, referral, partecipazione a eventi, messaggi), log tecnici (indirizzo IP, user agent) per finalità di sicurezza.</p>\n<h2>3. Finalità del trattamento</h2>\n<ul><li>Erogazione del servizio Kommunity (account, networking, eventi, messaggistica).</li><li>Adempimenti contrattuali e fiscali.</li><li>Sicurezza e prevenzione abusi.</li><li>Comunicazioni di servizio (email transazionali, notifiche).</li></ul>\n<h2>4. Base giuridica</h2>\n<p>Le basi giuridiche sono il contratto di servizio (art. 6.1.b GDPR), gli obblighi di legge (art. 6.1.c) e il legittimo interesse (art. 6.1.f) per la sicurezza della piattaforma.</p>\n<h2>5. Conservazione dei dati</h2>\n<p>Conserviamo i dati per il tempo necessario alle finalità sopra indicate e comunque per i termini di legge. Alla cancellazione dell\'account i dati vengono rimossi entro 30 giorni, salvo obblighi di legge.</p>\n<h2>6. Diritti dell\'interessato</h2>\n<p>Hai diritto di accesso, rettifica, cancellazione, limitazione, portabilità e opposizione (artt. 15-22 GDPR). Per esercitarli scrivi a <a href="mailto:{EMAIL_PRIVACY}">{EMAIL_PRIVACY}</a>. Hai inoltre diritto di reclamo presso il <a href="https://www.garanteprivacy.it/" target="_blank" rel="noopener noreferrer">Garante per la protezione dei dati personali</a>.</p>\n<h2>7. Comunicazione a terzi</h2>\n<p>I dati possono essere comunicati a fornitori tecnici (hosting, email, analytics) che agiscono come responsabili del trattamento. Non vendiamo i tuoi dati a terzi.</p>\n<h2>8. Cookie</h2>\n<p>Per i dettagli sui cookie consulta la nostra <a href="/pagina/cookie-policy">Cookie Policy</a>.</p>',
     'Informativa sulla privacy di Kommunity in conformità con il Regolamento UE 2016/679 (GDPR).',
     0, 1, 0, 1, 1,
     NOW(), NOW()),

-- 2) TERMINI E CONDIZIONI ─────────────────────────────────────────────────────
    ('Termini e Condizioni', 'termini',
     '<h1>Termini e Condizioni d\'uso</h1>\n<p>Ultimo aggiornamento: 30/04/2026.</p>\n<h2>1. Oggetto</h2>\n<p>I presenti Termini regolano l\'utilizzo della piattaforma Kommunity (di seguito "Servizio") fornita da <strong>{NOME_AZIENDA}</strong>. Registrandosi al Servizio l\'utente accetta integralmente i presenti Termini.</p>\n<h2>2. Account</h2>\n<p>L\'utente è responsabile della custodia delle proprie credenziali e di ogni attività compiuta tramite il proprio account. È vietato condividere l\'account con terzi.</p>\n<h2>3. Comportamenti vietati</h2>\n<ul><li>Pubblicare contenuti illeciti, offensivi, discriminatori o lesivi della privacy altrui.</li><li>Caricare materiale protetto da copyright senza diritto.</li><li>Spam, phishing o ingegneria sociale verso altri membri.</li><li>Tentativi di compromettere la sicurezza della piattaforma.</li></ul>\n<h2>4. Contenuti dell\'utente</h2>\n<p>Mantieni la titolarità dei contenuti che pubblichi. Concedi a Kommunity una licenza non esclusiva, gratuita e revocabile per ospitare e mostrare tali contenuti agli altri membri nei limiti del Servizio.</p>\n<h2>5. Abbonamenti e pagamenti</h2>\n<p>Le condizioni economiche degli abbonamenti sono indicate nella sezione apposita della piattaforma. Eventuali rinnovi automatici saranno preventivamente comunicati.</p>\n<h2>6. Sospensione e chiusura account</h2>\n<p>Kommunity si riserva il diritto di sospendere o chiudere account che violino i presenti Termini, dandone, ove possibile, preavviso all\'utente.</p>\n<h2>7. Limitazione di responsabilità</h2>\n<p>Il Servizio è fornito "as is". Nei limiti consentiti dalla legge, Kommunity non risponde di danni indiretti derivanti dall\'uso del Servizio.</p>\n<h2>8. Modifiche ai Termini</h2>\n<p>Eventuali modifiche saranno pubblicate su questa pagina e comunicate via email almeno 15 giorni prima dell\'entrata in vigore.</p>\n<h2>9. Legge applicabile e foro</h2>\n<p>I presenti Termini sono regolati dalla legge italiana. Foro competente esclusivo: {FORO_COMPETENTE}, salvo disposizioni inderogabili a tutela del consumatore.</p>',
     'Termini e Condizioni d\'uso della piattaforma Kommunity.',
     0, 1, 0, 2, 1,
     NOW(), NOW()),

-- 3) COOKIE POLICY ────────────────────────────────────────────────────────────
    ('Cookie Policy', 'cookie-policy',
     '<h1>Cookie Policy</h1>\n<p>Ultimo aggiornamento: 30/04/2026.</p>\n<h2>1. Cosa sono i cookie</h2>\n<p>I cookie sono piccoli file di testo che i siti visitati inviano al dispositivo dell\'utente, dove vengono memorizzati per essere ritrasmessi agli stessi siti alla visita successiva.</p>\n<h2>2. Cookie utilizzati da Kommunity</h2>\n<table><thead><tr><th>Nome</th><th>Tipologia</th><th>Finalità</th><th>Durata</th></tr></thead><tbody><tr><td><code>laravel_session</code></td><td>Tecnico</td><td>Sessione utente autenticato</td><td>120 minuti</td></tr><tr><td><code>XSRF-TOKEN</code></td><td>Tecnico</td><td>Protezione CSRF</td><td>Sessione</td></tr><tr><td><code>km_cookie_consent</code></td><td>Tecnico</td><td>Memorizza la tua preferenza sui cookie</td><td>1 anno</td></tr><tr><td><code>locale</code></td><td>Funzionale</td><td>Lingua scelta</td><td>1 anno</td></tr></tbody></table>\n<h2>3. Cookie di terze parti</h2>\n<p>Attualmente Kommunity non utilizza cookie di profilazione né tracker pubblicitari di terze parti. Se in futuro saranno introdotti, verrà richiesto un nuovo consenso esplicito.</p>\n<h2>4. Gestione del consenso</h2>\n<p>Al primo accesso viene mostrato un banner che ti permette di accettare tutti i cookie o solo quelli tecnici necessari. Puoi revocare il consenso in qualunque momento eliminando il cookie <code>km_cookie_consent</code> dalle impostazioni del browser: il banner ti verrà nuovamente mostrato.</p>\n<h2>5. Come disabilitare i cookie</h2>\n<p>Tutti i browser permettono di gestire e bloccare i cookie. Tieni presente che, disabilitando i cookie tecnici, alcune funzionalità della piattaforma potrebbero non funzionare correttamente.</p>\n<h2>6. Contatti</h2>\n<p>Per qualunque domanda relativa ai cookie scrivi a <a href="mailto:{EMAIL_PRIVACY}">{EMAIL_PRIVACY}</a>.</p>',
     'Informativa sui cookie utilizzati dalla piattaforma Kommunity.',
     0, 1, 0, 3, 1,
     NOW(), NOW())
ON DUPLICATE KEY UPDATE
    `title`            = VALUES(`title`),
    `meta_description` = VALUES(`meta_description`),
    `show_in_footer`   = VALUES(`show_in_footer`),
    `footer_order`     = VALUES(`footer_order`),
    `is_published`     = VALUES(`is_published`),
    `updated_at`       = NOW();

-- ─────────────────────────────────────────────────────────────────────────────
-- NOTA: le colonne `content` e `meta_description` NON sono nel UPDATE clause:
-- se la pagina esiste già con contenuto personalizzato, viene preservata.
-- Se vuoi forzare il reset del testo, decommenta queste righe nello UPDATE:
--     `content`          = VALUES(`content`),
-- ─────────────────────────────────────────────────────────────────────────────
