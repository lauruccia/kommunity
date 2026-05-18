# Piano operativo: Pianeti, video su richiesta, circuito banner

## Obiettivo

Rendere Kommunity realmente organizzata per Pianeti:

- ogni nuovo utente entra nel Pianeta di chi lo invita;
- i membri vedono e invitano nel proprio Pianeta;
- solo admin puo' spostare utenti o abilitarli a piu' Pianeti;
- ogni relazione di invito resta tracciata;
- i video profilo non sono pubblici in directory/profilo, ma si sbloccano con consenso reciproco;
- i banner diventano un prodotto vendibile con targeting per Pianeti, geografia, professioni e sezioni.

Nel codice attuale `Chapter` e' gia' il modello di Pianeta. Esistono gia' `ChapterInvitation`, `chapter_members`, `member_profiles.active_chapter_id`, `leader_id` sul Pianeta e un primo sistema `audience_type` sugli eventi.

## Pianeti e inviti

### Regola funzionale

Il Pianeta di registrazione deve essere determinato sempre da una delle due fonti:

1. invito diretto `ChapterInvitation`;
2. link referral personale dell'utente invitante.

Se un utente invita tramite referral semplice, il registrato eredita il `active_chapter_id` dell'invitante e viene inserito in `chapter_members` nello stesso Pianeta. Se l'invitante appartiene a piu' Pianeti, si usa il suo Pianeta attivo al momento della generazione del link.

### Modifiche consigliate

- Aggiungere `chapter_id` alla tracciabilita' referral/registrazione, o almeno salvare `registration_chapter_id` nel flusso di sessione quando viene letto `?ref=`.
- In `RegisteredUserController`, dopo la creazione utente:
  - se c'e' `chapter_invitation_token`, usare quello;
  - altrimenti, se c'e' inviter valido, assegnare il Pianeta attivo dell'inviter;
  - creare/aggiornare `chapter_members`;
  - salvare sempre `invited_by_user_id`.
- Bloccare nel profilo utente la modifica autonoma del Pianeta.
- In admin, lasciare la gestione multi-Pianeta nel relation manager `UserPlanetsRelationManager`.

### Ruoli

- Admin: crea Pianeti, nomina leader, sposta utenti, abilita multi-Pianeta, crea eventi globali o targettizzati.
- Leader Pianeta: invita solo nel proprio Pianeta, vede richieste/inviti del proprio Pianeta, crea eventi per il proprio Pianeta.
- Membro: invita altri membri solo nel proprio Pianeta tramite referral o richiesta al leader.

## Eventi per ruoli e audience

Il database supporta gia' eventi per Pianeta e professione. Manca il targeting per ruolo.

### Estensione minima

- Aggiungere tabella `event_role_targets` con `event_id` e `role_id`.
- Estendere `events.audience_type` con:
  - `by_role`;
  - `by_planet_and_role`;
  - `by_profession_and_role`;
  - `custom`.
- Aggiornare `Event::isVisibleTo()` e query `EventController@index`.
- Esporre in Filament `audience_type`, Pianeti target, professioni target e ruoli target.

Esempio: evento solo per leader Pianeti = `audience_type = by_role`, target role `leader-capitolo`.

## Video profilo su richiesta

### Proposta raccomandata

Usare un modello di "scambio video reciproco":

1. Utente A visita profilo di B.
2. Se B ha video, A vede un box bloccato: "Richiedi accesso alla videopresentazione".
3. A invia richiesta a B.
4. B accetta.
5. Da quel momento A vede il video di B e B vede il video di A.

Questa e' la scelta piu' chiara commercialmente e piu' sicura lato privacy, perche' il video diventa una relazione di consenso e non un contenuto pubblico.

### Regole

- Il proprietario vede sempre il proprio video.
- Admin puo' vedere e moderare tutto.
- La directory non deve mai mostrare thumbnail video nel pallino: solo avatar o iniziale.
- Il profilo pubblico mostra il video solo se esiste una relazione approvata.
- Se uno dei due utenti rimuove il video, l'accesso resta registrato ma non mostra contenuto.

### Struttura dati

Nuova tabella `profile_video_access_requests`:

- `id`
- `requester_id`
- `recipient_id`
- `status`: `pending`, `accepted`, `declined`, `revoked`
- `requested_at`
- `responded_at`
- `revoked_at`
- timestamps
- unique su `requester_id`, `recipient_id`

Per verificare l'accesso, basta cercare una richiesta `accepted` in entrambe le direzioni logiche:

- requester = A, recipient = B
- oppure requester = B, recipient = A

### UI

- Profilo membro: box video bloccato con CTA richiesta.
- Notifiche: "Mario ti chiede di scambiare la videopresentazione".
- Pagina notifiche o profilo: accetta/rifiuta.
- Area profilo personale: elenco accessi concessi con azione revoca.

## Circuito banner

### Concetto commerciale

Il banner non deve essere solo un'immagine caricata: deve essere una campagna con periodo, posizionamenti, target e metriche. Questo permette di vendere pacchetti chiari.

### Entita'

`advertisers`

- ragione sociale/nome cliente
- referente
- email
- telefono
- note commerciali

`banner_campaigns`

- advertiser_id
- titolo interno
- status: draft, scheduled, active, paused, ended
- starts_at, ends_at
- priority
- budget/price opzionale
- max_impressions opzionale
- max_clicks opzionale
- target_url
- open_in_new_tab

`banner_creatives`

- campaign_id
- image_desktop
- image_mobile
- alt_text
- headline opzionale
- placement_size: leaderboard, sidebar, card, in_feed

`banner_placements`

- key: directory_top, directory_sidebar, directory_in_feed, event_sidebar, dashboard_top, profile_sidebar
- label
- is_active

`banner_campaign_placement`

- campaign_id
- placement_id

Target pivot:

- `banner_campaign_chapter` per Pianeti;
- `banner_campaign_region`;
- `banner_campaign_city`;
- `banner_campaign_profession`;
- `banner_campaign_category`;
- opzionale `banner_campaign_role`.

Metriche:

`banner_impressions`

- campaign_id
- creative_id
- placement_key
- user_id nullable
- chapter_id nullable
- shown_at

`banner_clicks`

- campaign_id
- creative_id
- placement_key
- user_id nullable
- chapter_id nullable
- clicked_at

### Regole targeting

Una campagna e' visibile se:

- e' `active`;
- la data corrente e' tra `starts_at` e `ends_at`;
- il placement richiesto e' abilitato;
- il target corrisponde al contesto utente.

Se non ha target specifici, e' globale. Se ha piu' target, consiglio logica OR commerciale:

- mostra se combacia almeno un target tra Pianeta, area, professione, categoria.

Per campagne premium si puo' aggiungere una modalita' AND, ma non e' necessaria nella prima release.

### Posizionamenti vendibili

- Directory: testata sopra i risultati.
- Directory: sidebar sotto filtri.
- Directory: card in-feed ogni N profili.
- Eventi: sidebar/pannello dettaglio.
- Dashboard: fascia alta.
- Profilo membro: sidebar.

### Filament admin

Creare risorse:

- `AdvertiserResource`
- `BannerCampaignResource`
- `BannerPlacementResource`

Nel form campagna:

- date inizio/fine;
- stato;
- priorita';
- URL destinazione;
- upload creativita' desktop/mobile;
- selezione placement;
- selezione target Pianeti, regioni, citta', professioni, categorie, ruoli.

### Service applicativo

Creare `BannerService`:

- `forPlacement(string $placementKey, User $user, ?Chapter $chapter, int $limit = 1)`
- filtra campagne eleggibili;
- ordina per priorita' e rotazione;
- registra impression;
- genera click URL tracciato.

Rotazione iniziale: priorita' desc + random tra pari priorita'. In seguito: ponderazione per budget/impression.

### Endpoint

- `GET /banner/{campaign}/click?creative=...&placement=...`
- registra click e reindirizza a `target_url`.

## Roadmap

### Fase 1: sicurezza Pianeti

- Correggere registrazione da referral: assegnazione automatica al Pianeta dell'invitante.
- Rendere non modificabile il Pianeta lato utente.
- Verificare che directory, forum ed eventi filtrino per Pianeta attivo.
- Esporre bene in admin chi ha invitato chi.

### Fase 2: video privato

- Creare tabella richieste accesso video.
- Aggiungere policy/helper `canViewIntroVideo`.
- Aggiornare profilo membro con richiesta/accettazione.
- Aggiungere notifiche.

### Fase 3: eventi audience completa

- Aggiungere target ruoli.
- Aggiornare Filament EventResource.
- Testare evento solo leader.

### Fase 4: banner MVP

- Creare modelli/migration Filament.
- Inserire component Blade `<x-banner-placement key="directory_top" />`.
- Tracciare impression e click.
- Attivare placement directory top/sidebar.

### Fase 5: vendita e report

- Report per campagna: impression, click, CTR, target usati, periodo.
- Export CSV.
- Pacchetti commerciali: globale, per Pianeta, per area, per professione.
