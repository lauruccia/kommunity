<?php

return [
    /*
    |----------------------------------------------------------------------
    | Candidature di ammissione — Italiano
    |----------------------------------------------------------------------
    | Sezione "Candidati" in homepage, messaggi del form pubblico
    | ed email al candidato (ricevuta / approvata / rifiutata).
    */

    // ── Sezione homepage ──────────────────────────────────────────────────
    'home_badge'      => 'Accesso su selezione',
    'home_title_1'    => 'Non cerchiamo iscritti.',
    'home_title_2'    => 'Costruiamo relazioni che contano.',
    'home_text'       => 'Kommunity è una community a numero chiuso: professionisti e aziende selezionati uno a uno, per garantire relazioni di qualità e opportunità concrete. Ogni candidatura viene valutata personalmente.',
    'home_point_1'    => 'Profili verificati, nessun contatto casuale',
    'home_point_2'    => 'Pianeti territoriali con posti limitati per professione',
    'home_point_3'    => 'Referenze e collaborazioni tra membri selezionati',
    'home_cta'        => 'Invia la tua candidatura',
    'home_nav'        => 'Candidati',

    // ── Form ──────────────────────────────────────────────────────────────
    'form_title'          => 'Candidatura di ammissione',
    'form_subtitle'       => 'Compila i campi: il nostro team valuterà il tuo profilo e riceverai una risposta via email.',
    'form_name'           => 'Nome e cognome',
    'form_email'          => 'Email',
    'form_phone'          => 'Telefono',
    'form_type'           => 'Ti candidi come',
    'form_type_private'   => 'Privato / Professionista',
    'form_type_company'   => 'Azienda',
    'form_vat'            => 'Partita IVA',
    'form_vat_hint'       => 'Obbligatoria per le aziende, facoltativa per i privati',
    'form_profession'     => 'Professione / attività',
    'form_profession_ph'  => 'Es. Commercialista, agenzia di comunicazione…',
    'form_referrer'       => 'Chi ti ha fatto conoscere Kommunity?',
    'form_referrer_hint'  => 'Se un membro ti ha presentato, indicalo: le candidature presentate hanno una corsia preferenziale.',
    'form_referrer_ph'    => 'Nome e cognome, oppure "ricerca online", un evento…',
    'form_submit'         => 'Invia la candidatura',
    'form_privacy'        => 'Inviando la candidatura acconsenti al trattamento dei dati per la sola valutazione della richiesta.',

    // ── Esiti ─────────────────────────────────────────────────────────────
    'success_title' => 'Candidatura ricevuta',
    'success_text'  => 'Grazie: il tuo profilo è ora in valutazione. Ti risponderemo via email al più presto.',

    'error_already_member'  => 'Questa email è già registrata su Kommunity. Se non ricordi la password, usa "Password dimenticata" dalla pagina di accesso.',
    'error_already_pending' => 'Abbiamo già ricevuto una candidatura con questa email: è in fase di valutazione, ti risponderemo a breve.',

    // ── Validazione ───────────────────────────────────────────────────────
    'v_name_required'       => 'Inserisci nome e cognome.',
    'v_name_full'           => 'Inserisci sia il nome sia il cognome.',
    'v_email_required'      => 'Inserisci la tua email.',
    'v_email_valid'         => 'Inserisci un indirizzo email valido.',
    'v_phone_required'      => 'Inserisci il tuo numero di telefono.',
    'v_phone_valid'         => 'Inserisci un numero di telefono valido.',
    'v_type_required'       => 'Indica se ti candidi come privato o come azienda.',
    'v_vat_required'        => 'La Partita IVA è obbligatoria per le aziende.',
    'v_profession_required' => 'Indica la tua professione o attività.',

    // ── Email: candidatura ricevuta ───────────────────────────────────────
    'mail_received_subject'  => 'La tua candidatura a Kommunity è in valutazione',
    'mail_received_title'    => 'Candidatura ricevuta',
    'mail_received_greeting' => 'Ciao :name,',
    'mail_received_line1'    => 'grazie per aver richiesto l\'ammissione a Kommunity, la community a numero chiuso dove professionisti e aziende selezionati costruiscono relazioni di valore.',
    'mail_received_line2'    => 'Il nostro team sta valutando il tuo profilo. L\'accesso è su selezione: riceverai una risposta via email al più presto.',
    'mail_received_presenter'=> 'Ti presenta: :name',
    'mail_received_footer'   => 'Hai ricevuto questa email perché è stata inviata una candidatura a Kommunity con questo indirizzo. Se non sei stato tu, ignora questa email.',

    // ── Email: candidatura approvata ──────────────────────────────────────
    'mail_approved_subject'  => 'Benvenuto in Kommunity — la tua candidatura è stata approvata',
    'mail_approved_title'    => 'Sei dentro.',
    'mail_approved_greeting' => 'Ciao :name,',
    'mail_approved_line1'    => 'abbiamo il piacere di comunicarti che la tua candidatura è stata approvata: da oggi fai parte di Kommunity.',
    'mail_approved_planet'   => 'Sei stato ammesso nel Pianeta :planet.',
    'mail_approved_line2'    => 'Per attivare il tuo account imposta ora la tua password personale:',
    'mail_approved_button'   => 'Imposta la password ed entra',
    'mail_approved_expiry'   => 'Per sicurezza il link scade dopo :minutes minuti. Se è scaduto, vai su ":forgot" nella pagina di accesso e inserisci questa email per riceverne uno nuovo.',
    'mail_approved_forgot'   => 'Password dimenticata',
    'mail_approved_line3'    => 'Una volta dentro, completa il tuo profilo: è il tuo biglietto da visita verso gli altri membri.',

    // ── Email: candidatura non accolta ────────────────────────────────────
    'mail_rejected_subject'  => 'La tua candidatura a Kommunity',
    'mail_rejected_title'    => 'Grazie per la tua candidatura',
    'mail_rejected_greeting' => 'Ciao :name,',
    'mail_rejected_line1'    => 'grazie per l\'interesse verso Kommunity. Dopo un\'attenta valutazione, al momento non ci è possibile accogliere la tua candidatura.',
    'mail_rejected_reason'   => 'Nota del team: :reason',
    'mail_rejected_line2'    => 'Kommunity cresce per selezione e i posti nei Pianeti sono limitati: potrai ripresentare la candidatura in futuro, anche tramite la presentazione di un membro.',
];
