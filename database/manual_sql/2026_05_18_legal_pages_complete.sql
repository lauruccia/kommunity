-- ─────────────────────────────────────────────────────────────────────────────
-- Kommunity — Pagine legali complete (Privacy Policy / Termini / Cookie Policy)
-- Titolare: KNM Srl · Via Eurialo, 56 · 00181 Roma (IT) · P.IVA 13273091002
-- Da eseguire tramite phpMyAdmin → SQL
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO `pages`
    (`title`, `slug`, `content`, `meta_description`,
     `show_in_nav`, `show_in_footer`, `nav_order`, `footer_order`, `is_published`,
     `created_at`, `updated_at`)
VALUES

-- ── 1. PRIVACY POLICY ────────────────────────────────────────────────────────
(
  'Privacy Policy',
  'privacy',
  '<h1>Informativa sul trattamento dei dati personali</h1>
<p><em>Ultimo aggiornamento: 18 maggio 2026</em></p>

<p>La presente Informativa è resa ai sensi degli artt. 13 e 14 del Regolamento (UE) 2016/679 del Parlamento europeo e del Consiglio del 27 aprile 2016 (di seguito "<strong>GDPR</strong>") e descrive le modalità con cui KNM Srl tratta i dati personali degli utenti che accedono e utilizzano la piattaforma Kommunity.</p>

<h2>1. Titolare del trattamento</h2>
<p><strong>KNM Srl</strong><br>
Via Eurialo, 56 — 00181 Roma (RM)<br>
P.IVA 13273091002<br>
Email: <a href="mailto:privacy@kommunity.it">privacy@kommunity.it</a></p>

<h2>2. Categorie di dati trattati</h2>
<p>Il Titolare tratta le seguenti categorie di dati personali:</p>
<ul>
  <li><strong>Dati identificativi e di contatto</strong>: nome e cognome, indirizzo e-mail, numero di telefono, eventuale numero WhatsApp;</li>
  <li><strong>Dati di profilo professionale</strong>: ragione sociale o nome commerciale, professione, settore di attività, biografia, obiettivi di networking, immagine del profilo, immagini della galleria, video di presentazione, link ai profili social e al sito web;</li>
  <li><strong>Dati di utilizzo della piattaforma</strong>: accessi, richieste di incontri 1:1, referral scambiati, iscrizioni a eventi, messaggi privati, interazioni nel forum, notifiche push;</li>
  <li><strong>Dati tecnici</strong>: indirizzo IP, tipo e versione del browser, sistema operativo, log di accesso, token di sessione.</li>
</ul>
<p>Il Titolare non tratta categorie particolari di dati ai sensi dell'art. 9 GDPR, salvo diversa indicazione esplicita.</p>

<h2>3. Finalità e basi giuridiche del trattamento</h2>
<table>
  <thead><tr><th>Finalità</th><th>Base giuridica (art. 6 GDPR)</th></tr></thead>
  <tbody>
    <tr><td>Registrazione, gestione dell'account e fornitura del servizio Kommunity</td><td>Esecuzione del contratto (art. 6.1.b)</td></tr>
    <tr><td>Gestione degli abbonamenti e degli adempimenti fiscali e contrattuali</td><td>Obbligo legale (art. 6.1.c) ed esecuzione del contratto (art. 6.1.b)</td></tr>
    <tr><td>Comunicazioni transazionali di servizio (conferma iscrizione, reset password, notifiche di sistema)</td><td>Esecuzione del contratto (art. 6.1.b)</td></tr>
    <tr><td>Sicurezza della piattaforma, prevenzione di frodi e abusi</td><td>Legittimo interesse del Titolare (art. 6.1.f)</td></tr>
    <tr><td>Miglioramento e ottimizzazione della piattaforma tramite analisi aggregate e anonimizzate</td><td>Legittimo interesse del Titolare (art. 6.1.f)</td></tr>
    <tr><td>Adempimento di obblighi di legge, regolamentari o di vigilanza</td><td>Obbligo legale (art. 6.1.c)</td></tr>
  </tbody>
</table>

<h2>4. Modalità del trattamento e periodo di conservazione</h2>
<p>I dati sono trattati con strumenti informatici e telematici, con logiche strettamente correlate alle finalità indicate, e con misure di sicurezza adeguate a ridurre i rischi di accesso non autorizzato, perdita o alterazione.</p>
<p>I dati vengono conservati per il tempo strettamente necessario alle finalità per cui sono stati raccolti e, in ogni caso:</p>
<ul>
  <li>Per la durata del rapporto contrattuale e, successivamente, per il periodo prescritto dalla normativa fiscale e civilistica vigente (generalmente <strong>10 anni</strong> dalla cessazione del rapporto);</li>
  <li>I dati di traffico e log tecnici per finalità di sicurezza sono conservati per un massimo di <strong>12 mesi</strong>;</li>
  <li>In caso di cancellazione dell'account su richiesta dell'interessato, i dati di profilo vengono rimossi entro <strong>30 giorni</strong>, salvo obblighi di conservazione previsti dalla legge.</li>
</ul>

<h2>5. Comunicazione a terzi e trasferimenti</h2>
<p>I dati personali possono essere comunicati a soggetti terzi che, nell'ambito della prestazione dei servizi richiesti, agiscono in qualità di <strong>responsabili del trattamento</strong> ai sensi dell'art. 28 GDPR, tra cui:</p>
<ul>
  <li>Fornitori di servizi di hosting e infrastruttura cloud;</li>
  <li>Fornitori di servizi di posta elettronica transazionale;</li>
  <li>Fornitori di servizi di analisi delle prestazioni della piattaforma.</li>
</ul>
<p>I dati non vengono venduti, ceduti o comunicati a terzi per finalità di marketing di terze parti. Qualora i fornitori siano stabiliti in Paesi extra-UE, il trasferimento avviene nel rispetto degli artt. 44 ss. GDPR, mediante la stipula di clausole contrattuali standard approvate dalla Commissione europea.</p>

<h2>6. Diritti dell'interessato</h2>
<p>L'interessato ha il diritto di:</p>
<ul>
  <li><strong>Accesso</strong> (art. 15 GDPR): ottenere conferma del trattamento e copia dei propri dati;</li>
  <li><strong>Rettifica</strong> (art. 16 GDPR): correggere dati inesatti o incompleti;</li>
  <li><strong>Cancellazione</strong> (art. 17 GDPR): ottenere la rimozione dei propri dati, nei limiti previsti dalla legge;</li>
  <li><strong>Limitazione</strong> (art. 18 GDPR): richiedere la sospensione del trattamento in determinati casi;</li>
  <li><strong>Portabilità</strong> (art. 20 GDPR): ricevere i propri dati in formato strutturato e leggibile da dispositivo automatico;</li>
  <li><strong>Opposizione</strong> (art. 21 GDPR): opporsi al trattamento fondato sul legittimo interesse del Titolare;</li>
  <li><strong>Revoca del consenso</strong> (ove applicabile), senza pregiudizio per la liceità del trattamento precedente alla revoca.</li>
</ul>
<p>Le richieste possono essere inviate a <a href="mailto:privacy@kommunity.it">privacy@kommunity.it</a>. Il Titolare risponderà entro 30 giorni dalla ricezione, prorogabili di ulteriori 60 giorni in caso di particolare complessità.</p>
<p>L'interessato ha inoltre il diritto di proporre reclamo all'autorità di controllo competente: <a href="https://www.garanteprivacy.it/" target="_blank" rel="noopener noreferrer">Garante per la protezione dei dati personali</a>, Piazza Venezia 11 — 00187 Roma.</p>

<h2>7. Cookie</h2>
<p>Per le informazioni dettagliate sull'utilizzo dei cookie si rinvia alla <a href="/pagina/cookie-policy">Cookie Policy</a>.</p>

<h2>8. Modifiche alla presente Informativa</h2>
<p>Il Titolare si riserva il diritto di aggiornare la presente Informativa in qualsiasi momento. Le modifiche rilevanti saranno comunicate agli utenti registrati tramite e-mail o avviso in piattaforma almeno <strong>15 giorni</strong> prima dell'entrata in vigore.</p>',
  'Informativa sul trattamento dei dati personali di Kommunity — KNM Srl, ai sensi del Regolamento UE 2016/679 (GDPR).',
  0, 1, 0, 1, 1,
  NOW(), NOW()
),

-- ── 2. TERMINI E CONDIZIONI ───────────────────────────────────────────────────
(
  'Termini e condizioni',
  'termini',
  '<h1>Termini e Condizioni d''uso della piattaforma Kommunity</h1>
<p><em>Ultimo aggiornamento: 18 maggio 2026</em></p>

<p>I presenti Termini e Condizioni d''uso (di seguito "<strong>Termini</strong>") disciplinano l''accesso e l''utilizzo della piattaforma Kommunity (di seguito "<strong>Piattaforma</strong>" o "<strong>Servizio</strong>"), resa disponibile da <strong>KNM Srl</strong>, con sede legale in Via Eurialo, 56 — 00181 Roma (RM), P.IVA 13273091002 (di seguito "<strong>Gesellschaft</strong>" o "<strong>Kommunity</strong>").</p>
<p>Completando la registrazione alla Piattaforma, l''utente dichiara di aver letto, compreso e accettato integralmente i presenti Termini. L''accesso alla Piattaforma è consentito esclusivamente a soggetti che abbiano compiuto i <strong>18 anni di età</strong> e che agiscano nell''ambito della propria attività professionale o imprenditoriale.</p>

<h2>1. Oggetto del Servizio</h2>
<p>Kommunity è una piattaforma digitale di networking professionale che consente ai propri iscritti di creare un profilo professionale, connettersi con altri professionisti, partecipare a eventi e incontri, scambiare segnalazioni e opportunità di business, interagire attraverso forum tematici e messaggistica privata, nonché usufruire di ulteriori funzionalità rese disponibili nel tempo da Kommunity.</p>

<h2>2. Registrazione e account</h2>
<p>Per accedere al Servizio è necessario registrarsi fornendo informazioni accurate, aggiornate e veritiere. L''utente è responsabile:</p>
<ul>
  <li>della riservatezza delle proprie credenziali di accesso (username e password);</li>
  <li>di ogni attività compiuta tramite il proprio account;</li>
  <li>della tempestiva comunicazione a Kommunity di qualsiasi utilizzo non autorizzato del proprio account.</li>
</ul>
<p>È espressamente vietato condividere le credenziali di accesso con terzi o creare account per conto di altri soggetti senza espressa autorizzazione.</p>

<h2>3. Abbonamenti e pagamenti</h2>
<p>L''accesso a determinate funzionalità della Piattaforma è subordinato alla sottoscrizione di un piano di abbonamento a pagamento. Le condizioni economiche, la durata e le modalità di pagamento dei piani disponibili sono indicate nella sezione "Abbonamenti" della Piattaforma.</p>
<p>Il mancato pagamento del corrispettivo dovuto può comportare la sospensione o la revoca dell''accesso alle funzionalità riservate agli abbonati. L''attivazione dell''abbonamento avviene a seguito della verifica del pagamento da parte di Kommunity.</p>

<h2>4. Condotta degli utenti e contenuti vietati</h2>
<p>Nell''utilizzo della Piattaforma, l''utente si impegna a non:</p>
<ul>
  <li>pubblicare, caricare o diffondere contenuti illeciti, offensivi, discriminatori, diffamatori, osceni o lesivi della dignità altrui;</li>
  <li>violare diritti di proprietà intellettuale di terzi, ivi inclusi brevetti, marchi, segreti commerciali, diritti d''autore o altri diritti proprietari;</li>
  <li>porre in essere attività di spam, phishing, social engineering o qualsiasi altra pratica ingannevole nei confronti degli altri utenti;</li>
  <li>raccogliere dati personali di altri utenti senza il loro consenso;</li>
  <li>tentare di compromettere la sicurezza, l''integrità o la disponibilità della Piattaforma o dei relativi sistemi;</li>
  <li>utilizzare la Piattaforma per finalità concorrenziali rispetto a Kommunity senza preventiva autorizzazione scritta.</li>
</ul>

<h2>5. Proprietà intellettuale</h2>
<p>L''utente mantiene la titolarità dei contenuti che pubblica sulla Piattaforma e concede a Kommunity una <strong>licenza non esclusiva, gratuita, revocabile e non trasferibile</strong> per ospitare, visualizzare, riprodurre e distribuire tali contenuti all''interno della Piattaforma, nei limiti strettamente necessari all''erogazione del Servizio.</p>
<p>Tutti gli altri contenuti presenti sulla Piattaforma — ivi inclusi marchi, loghi, grafica, software, testi e documentazione — sono di proprietà esclusiva di KNM Srl o dei rispettivi licenziatari e sono tutelati dalle vigenti norme in materia di proprietà intellettuale. È vietato qualsiasi utilizzo non autorizzato.</p>

<h2>6. Disponibilità del Servizio</h2>
<p>Kommunity si impegna a garantire la disponibilità della Piattaforma in misura ragionevolmente elevata, ma non offre garanzie di continuità assoluta del Servizio. Interventi di manutenzione programmata o straordinaria potranno comportare interruzioni temporanee, previamente comunicate ove possibile.</p>

<h2>7. Sospensione e chiusura dell''account</h2>
<p>Kommunity si riserva il diritto di sospendere o chiudere, con effetto immediato o previo avviso, l''account di qualsiasi utente che violi i presenti Termini, adotti comportamenti contrari alla legge o comprometta il corretto funzionamento della Piattaforma o la sicurezza degli altri utenti. L''utente può richiedere in qualsiasi momento la cancellazione del proprio account scrivendo a <a href="mailto:info@kommunity.it">info@kommunity.it</a>.</p>

<h2>8. Limitazione di responsabilità</h2>
<p>Nella misura massima consentita dalla legge applicabile, Kommunity non sarà responsabile per danni indiretti, incidentali, speciali, punitivi o consequenziali derivanti dall''utilizzo o dall''impossibilità di utilizzo della Piattaforma, inclusi, a titolo esemplificativo, perdita di profitto, perdita di dati o interruzione dell''attività.</p>
<p>Kommunity non è responsabile per i contenuti pubblicati dagli utenti né per le interazioni tra gli stessi, ivi incluse le operazioni commerciali o di collaborazione eventualmente concluse tramite la Piattaforma.</p>

<h2>9. Modifiche ai Termini</h2>
<p>Kommunity si riserva il diritto di modificare i presenti Termini in qualsiasi momento. Le modifiche sostanziali saranno comunicate agli utenti registrati tramite e-mail o avviso in piattaforma con un preavviso di almeno <strong>15 giorni</strong> rispetto alla data di entrata in vigore. Il proseguimento nell''utilizzo della Piattaforma dopo tale data costituirà accettazione delle modifiche.</p>

<h2>10. Legge applicabile e foro competente</h2>
<p>I presenti Termini sono regolati dalla legge italiana. Per qualsiasi controversia relativa all''interpretazione, validità o esecuzione dei presenti Termini, le parti eleggono la competenza esclusiva del <strong>Foro di Roma</strong>, fatto salvo quanto diversamente previsto da norme inderogabili a tutela del consumatore.</p>

<h2>11. Contatti</h2>
<p>Per qualsiasi comunicazione relativa ai presenti Termini: <a href="mailto:info@kommunity.it">info@kommunity.it</a> — KNM Srl, Via Eurialo 56, 00181 Roma.</p>',
  'Termini e Condizioni d''uso della piattaforma Kommunity — KNM Srl.',
  0, 1, 0, 2, 1,
  NOW(), NOW()
),

-- ── 3. COOKIE POLICY ─────────────────────────────────────────────────────────
(
  'Cookie Policy',
  'cookie-policy',
  '<h1>Cookie Policy</h1>
<p><em>Ultimo aggiornamento: 18 maggio 2026</em></p>

<p>La presente Cookie Policy descrive le tipologie di cookie e tecnologie analoghe utilizzate dalla piattaforma Kommunity, gestita da <strong>KNM Srl</strong> (Via Eurialo, 56 — 00181 Roma, P.IVA 13273091002), e le modalità con cui è possibile gestire le proprie preferenze.</p>
<p>La presente Policy integra l''<a href="/pagina/privacy">Informativa sulla privacy</a> e va letta congiuntamente ad essa.</p>

<h2>1. Cosa sono i cookie</h2>
<p>I cookie sono piccoli file di testo che un sito web invia al dispositivo dell''utente (computer, smartphone, tablet) al momento della visita. Vengono memorizzati nel browser e ritrasmessi al sito nelle visite successive, consentendo di riconoscere l''utente e di ricordare le sue preferenze.</p>
<p>Tecnologie analoghe ai cookie includono pixel, web beacon, local storage e altre forme di memorizzazione locale.</p>

<h2>2. Cookie utilizzati da Kommunity</h2>
<p>Kommunity utilizza esclusivamente cookie tecnici necessari al corretto funzionamento della piattaforma. Non vengono impiegati cookie di profilazione né tracker pubblicitari di terze parti.</p>

<table>
  <thead>
    <tr>
      <th>Nome del cookie</th>
      <th>Tipologia</th>
      <th>Finalità</th>
      <th>Durata</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><code>laravel_session</code></td>
      <td>Tecnico necessario</td>
      <td>Mantiene attiva la sessione dell''utente autenticato. Senza questo cookie non è possibile accedere all''area riservata.</td>
      <td>120 minuti (o fino alla chiusura del browser)</td>
    </tr>
    <tr>
      <td><code>XSRF-TOKEN</code></td>
      <td>Tecnico necessario</td>
      <td>Protezione contro gli attacchi di tipo Cross-Site Request Forgery (CSRF). Obbligatorio per la sicurezza delle richieste al server.</td>
      <td>Sessione</td>
    </tr>
    <tr>
      <td><code>km_cookie_consent</code></td>
      <td>Tecnico funzionale</td>
      <td>Memorizza la scelta dell''utente in merito ai cookie, in modo da non riproporre il banner nelle visite successive.</td>
      <td>12 mesi</td>
    </tr>
    <tr>
      <td><code>locale</code></td>
      <td>Tecnico funzionale</td>
      <td>Memorizza la lingua dell''interfaccia selezionata dall''utente.</td>
      <td>12 mesi</td>
    </tr>
  </tbody>
</table>

<h2>3. Cookie di terze parti</h2>
<p>Alla data di aggiornamento della presente Policy, Kommunity <strong>non utilizza</strong> cookie di terze parti per finalità di profilazione, remarketing o analisi comportamentale. Qualora in futuro si rendesse necessario introdurre tali cookie, gli utenti saranno preventivamente informati e verrà richiesto il consenso esplicito prima della loro installazione.</p>

<h2>4. Base giuridica</h2>
<p>I cookie tecnici strettamente necessari (come <code>laravel_session</code> e <code>XSRF-TOKEN</code>) sono installati senza necessità di consenso, in quanto indispensabili all''erogazione del Servizio richiesto dall''utente, ai sensi dell''art. 122, comma 1, del D.Lgs. 196/2003 (Codice Privacy) e del considerando 25 della Direttiva ePrivacy.</p>
<p>I cookie funzionali (<code>km_cookie_consent</code>, <code>locale</code>) sono installati sulla base del consenso espresso dall''utente tramite il banner cookies.</p>

<h2>5. Gestione e revoca del consenso</h2>
<p>Al primo accesso alla Piattaforma, viene mostrato un banner che consente di:</p>
<ul>
  <li>accettare tutti i cookie (tecnici e funzionali);</li>
  <li>accettare solo i cookie strettamente necessari.</li>
</ul>
<p>L''utente può modificare o revocare le proprie preferenze in qualsiasi momento eliminando il cookie <code>km_cookie_consent</code> dalle impostazioni del proprio browser: il banner verrà nuovamente visualizzato alla visita successiva.</p>

<h2>6. Come disabilitare i cookie dal browser</h2>
<p>Tutti i principali browser consentono di gestire, bloccare o eliminare i cookie. Si ricorda che la disabilitazione dei cookie tecnici necessari può impedire o limitare il corretto utilizzo della Piattaforma. Per le istruzioni specifiche:</p>
<ul>
  <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener noreferrer">Google Chrome</a></li>
  <li><a href="https://support.mozilla.org/it/kb/Eliminare%20i%20cookie" target="_blank" rel="noopener noreferrer">Mozilla Firefox</a></li>
  <li><a href="https://support.apple.com/it-it/guide/safari/sfri11471/mac" target="_blank" rel="noopener noreferrer">Apple Safari</a></li>
  <li><a href="https://support.microsoft.com/it-it/microsoft-edge/eliminare-i-cookie-in-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener noreferrer">Microsoft Edge</a></li>
</ul>

<h2>7. Contatti</h2>
<p>Per qualsiasi domanda relativa alla presente Cookie Policy, scrivere a <a href="mailto:privacy@kommunity.it">privacy@kommunity.it</a>.</p>',
  'Cookie Policy della piattaforma Kommunity — tipologie di cookie utilizzati e modalità di gestione del consenso.',
  0, 1, 0, 3, 1,
  NOW(), NOW()
)

ON DUPLICATE KEY UPDATE
    `title`            = VALUES(`title`),
    `content`          = VALUES(`content`),
    `meta_description` = VALUES(`meta_description`),
    `show_in_footer`   = VALUES(`show_in_footer`),
    `footer_order`     = VALUES(`footer_order`),
    `is_published`     = VALUES(`is_published`),
    `updated_at`       = NOW();
