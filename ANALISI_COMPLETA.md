# ANALISI COMPLETA — KOMMUNITY
> Redatta il 29 aprile 2026 · Senior Laravel Dev + Product Manager + UX Designer + QA

---

## A. SINTESI GENERALE

### Che cosa fa il sistema
Kommunity è una **piattaforma SaaS di networking professionale** per il mercato italiano. Permette ai professionisti di connettersi, fare incontri one-to-one, partecipare a eventi, dialogare in un forum, scambiarsi referral e gestire un mini-sito personale (Onepage). L'organizzazione territoriale avviene tramite **Pianeti** (capitoli locali), ognuno con un limite di membri per professione. Il pannello amministrativo è gestito tramite Filament v4 con ruoli e permessi Spatie.

### Stack tecnologico
- **Laravel 12** + Breeze (auth) + Filament 4 (admin) + Spatie Permission
- **Blade** + Alpine.js + Tailwind CSS + Vite
- **SQLite** (sviluppo) / **MySQL** (produzione via cPanel)
- Hosting condiviso cPanel senza SSH stabile
- Nessuna API, nessun WebSocket, nessuna payment gateway reale

### Livello di maturità attuale
Il progetto è in uno stadio di **prototipo avanzato / beta privata**. Le funzionalità core ci sono tutte, ma il codice ha molti segnali di sviluppo rapido e non rifinito: CSS inline in ogni vista, nessuna Policy, nessun middleware di onboarding, nessuna notifica email per la maggior parte degli eventi, query non ottimizzate, e una forte incoerenza visiva tra le sezioni.

### Giudizio complessivo
Il sistema ha una **base solida** (modelli ben strutturati, relazioni Eloquent corrette, buona separazione di ruoli) ma necessita di lavoro significativo prima di essere consegnato a un cliente pagante. I rischi principali sono tecnici (performance, sicurezza) e di prodotto (assenza di onboarding, incoerenza UX, mancanza di notifiche).

### Pronto per il primo cliente?
**No. Non ancora.** Mancano almeno 8–10 fix critici/urgenti.

### Principali rischi
1. Nessun onboarding guidato → utenti abbandonano dopo la registrazione
2. Nessuna notifica email per azioni chiave → il prodotto sembra "morto"
3. CSS duplicato in ogni vista → incoerenza visiva grave e impossibile da manutenere
4. Nessuna Policy di autorizzazione → sicurezza basata su `abort_unless` sparsi
5. Query non paginate in ConversationController → crash garantito con 50+ conversazioni
6. Abbonamenti manuali senza payment gateway → non scalabile

---

## B. TABELLA PROBLEMI TROVATI

| Area | File / Schermata | Problema | Gravità | Impatto | Soluzione |
|------|-----------------|----------|---------|---------|-----------|
| **Architettura CSS** | Tutte le viste | CSS inline `<style>` in ogni view con variabili ridefinite diverse | ALTA | Incoerenza visiva, impossibile manutenere | Centralizzare in `app.css` con un design system unico |
| **Sicurezza** | Tutte le route | Nessuna Policy Laravel. Auth via `abort_unless()` sparsi | ALTA | Rischio bypass se logica cambia | Creare Policy per ogni model |
| **Performance** | `ConversationController` | Tutte le conversazioni + tutti i messaggi caricate in memoria senza paginazione | ALTA | Crash/timeout con utenti reali | Paginare, caricare solo l'ultimo messaggio |
| **Performance** | `DirectoryController` | `->inRandomOrder()` senza cache | ALTA | Lento su tabelle grandi, non reproducibile | Sostituire con ordinamento casuale cacciato |
| **Performance** | `layouts/navigation.blade.php` | Query `SubscriptionPlan::exists()` ad ogni richiesta | ALTA | Query extra su ogni page load | Cache con `remember()` o view composer |
| **Funzionale** | Registrazione → Dashboard | Nessun middleware di onboarding. Utente accede a tutto senza profilo | ALTA | Utenti con profilo vuoto navigano in directory e forum | Creare middleware `EnsureOnboardingComplete` |
| **Sicurezza** | Form upload | Nessun rate limiting su POST di messaggi, forum, referral | ALTA | Spam, flooding | Aggiungere `throttle` su route critiche |
| **File spurii** | Root del progetto | File `each(function` nella root — artefatto di copia incolla | ALTA | Potenziale errore in produzione | Eliminare immediatamente |
| **Performance** | `auth/register.blade.php` | 3 query `SiteSetting::get()` non cachate ad ogni caricamento | MEDIA | Rallenta la pagina di registrazione | Cachare con `Cache::remember()` |
| **Tecnico** | `ProfileController::update()` | `'is_visible_in_directory' => true` hardcoded — admin non può renderlo invisibile | MEDIA | Admin perde controllo sulla visibilità | Rimuovere il forzamento hardcoded |
| **Tecnico** | `ProfileController::update()` | `status` riscritto ad ogni salvataggio → sovrascrive modifiche admin | MEDIA | Admin imposta `active`, utente salva e torna `pending_approval` | Non sovrascrivere status se già `active` |
| **Tecnico** | `MemberProfile` | Doppia colonna legacy: `category_id` FK + `categories` M2M / `profession_id` + `professions` M2M | MEDIA | Confusione, dato duplicato | Deprecare le FK legacy, usare solo M2M |
| **UX** | Tutte le sezioni | 4 design system visivi diversi (home, dashboard, forum, 1:1) | ALTA | Utente disorientato, brand non riconoscibile | Unificare in un solo sistema di token |
| **UX** | Profilo | Unico form gigante con 20+ campi senza step, senza progress bar | ALTA | Abbandono durante onboarding | Dividere in step con wizard |
| **UX** | Navigation | 7-8 voci nel menu principale senza icone, senza badge notifiche | MEDIA | Difficile navigazione, specialmente mobile | Ridurre a 5 voci + notifiche badge |
| **Funzionale** | Email | Nessuna notifica email per: messaggio ricevuto, 1:1 accettato, reply forum | ALTA | Il prodotto sembra inattivo, utenti non tornano | Implementare notifiche via Laravel Mail |
| **Funzionale** | Abbonamenti | Nessun payment gateway. Tutto manuale | ALTA | Non scalabile, errori umani | Integrare Stripe o PayPal |
| **Funzionale** | Forum | Nessuna notifica reply al thread author | MEDIA | Engagement basso | Notifica email + in-app |
| **Funzionale** | Dashboard | Filament Widgets vuoti (`.gitkeep`) — dashboard admin senza KPI | MEDIA | Admin cieco sui dati | Aggiungere widget con statistiche chiave |
| **Sicurezza** | `.env.example` | `LOG_LEVEL=debug` nel file di esempio | BASSA | Se copiato in produzione, log eccessivi con dati sensibili | Cambiare a `LOG_LEVEL=warning` |
| **Pulizia** | `resources/views/` | 7 file `.bladeOLD.php`, `backup`, `- Copia` nelle views | BASSA | Confusione, rischio di modificare il file sbagliato | Eliminare tutti i file di backup |
| **Tecnico** | `EventController::index()` | Metodo da 245 righe con logica complessa inline | MEDIA | Impossibile testare, impossibile manutenere | Estrarre in servizi/action class |
| **UX** | Conversazioni | Messaggi non paginati — carica tutto all'apertura | MEDIA | Lento con conversazioni lunghe | Paginazione o lazy load |
| **UX** | Stati vuoti | Nessuno stato vuoto disegnato per: nessun membro, nessun thread, nessun evento | MEDIA | Utente nuovo vede pagine vuote senza guida | Progettare empty state con CTA |

---

## C. ANALISI TECNICA DETTAGLIATA

### C1. Struttura generale — BUONA
La struttura è coerente con le convenzioni Laravel. Controller, Model, Enum, Support class sono ben separati. Il pannello Filament è ben organizzato con Resource, Pages e RelationManager. I Migration sono numerosi ma ordinati cronologicamente. I Seeder esistono (ItalianGeographySeeder).

### C2. CSS inline in ogni vista — CRITICO
**Problema:** Ogni view principale ha un blocco `<style>` da 50–300 righe con variabili CSS ridefinite. Le variabili hanno nomi diversi per ogni sezione:
- Home: `--brand`, `--teal`, `--bg`
- Dashboard: `--km-dark`, `--km-green`
- Forum: `--kf-bg`, `--kf-green`
- One-to-One: `--km-dark`, `--km-green` (simili ma non identici alla dashboard)
- Referral: proprie variabili

**Perché è un problema:** Il brand non è riconoscibile. Ogni modifica al colore principale richiede di toccare 10+ file. Impossibile costruire un design system incrementale.

**Soluzione:**
```css
/* app.css — UN SOLO sistema di token */
:root {
  --k-bg: #020b12;
  --k-bg-2: #031822;
  --k-brand: #8BC53F;
  --k-brand-dark: #5f9d42;
  --k-teal: #2DD4BF;
  --k-text: #F8FAFC;
  --k-muted: #AAB7C4;
  --k-line: rgba(255,255,255,.12);
}
```
Poi eliminare i blocchi `<style>` inline da ogni vista e usare classi Tailwind + token CSS.

### C3. Nessuna Policy di autorizzazione — ALTO RISCHIO
**Problema:** Non esiste nemmeno la cartella `app/Policies/`. L'autorizzazione avviene con `abort_unless()` e `abort_if()` sparsi nei controller. Se la logica cambia in un punto, bisogna ricordare di aggiornarla in ogni controller manualmente.

**Esempio di rischio nel ProfileController:**
```php
// Questa riga può essere bypassata se il route binding cambia
abort_unless($memberGalleryImage->user_id === $request->user()->id, 403);
```

**Soluzione:** Creare almeno:
- `ConversationPolicy` (solo partecipanti leggono/scrivono)
- `OneToOneRequestPolicy` (solo requester/recipient agiscono)
- `ReferralPolicy` (solo sender/recipient aggiornano)
- `EventPolicy` (solo organizer/admin cancellano/modificano)

### C4. N+1 e query non ottimizzate

**ConversationController — CRITICO:**
```php
// PROBLEMA: carica TUTTE le conversazioni con TUTTI i messaggi
$conversations = Conversation::query()
    ->with(['participants.memberProfile', 'messages.user'])  // N+1 su messaggi
    ->whereHas('participants', fn ($query) => $query->where('users.id', $user->id))
    ->get();  // nessuna paginazione

// Poi filtra in memoria
$conversations = $conversations->map(function ($conv) { ... })->filter(...);
```
Con 100 conversazioni e 50 messaggi ciascuna = 5.000 oggetti in RAM.

**Soluzione:**
```php
$conversations = Conversation::query()
    ->with(['participants.memberProfile'])
    ->withLatestMessage()  // scope custom che carica solo l'ultimo messaggio
    ->whereHas('participants', fn ($q) => $q->where('users.id', $user->id))
    ->paginate(20);
```

**DirectoryController — inRandomOrder():**
```php
->inRandomOrder()  // Non usa indici, full table scan ad ogni richiesta
->paginate(12)
```
**Soluzione:** Usare `Cache::remember('directory_order_'.date('H'), 60*60, fn() => MemberProfile::pluck('id')->shuffle())` e poi filtrare.

**Navigation — query ad ogni page load:**
```php
// Questo si esegue ad ogni richiesta, su ogni pagina
$hasActiveSubscriptionPlans = \App\Models\SubscriptionPlan::query()
    ->where('is_active', true)->exists();
```
**Soluzione:** View composer con `Cache::remember('has_plans', 3600, ...)`.

### C5. `ProfileController::update()` — problemi di logica
Due comportamenti rischiosi:

```php
// 1. Hardcoded: admin non può mai nascondere un membro via profilo
'is_visible_in_directory' => true,

// 2. Status riscritto ad ogni salvataggio del profilo
'status' => $request->boolean('onboarding_completed') ? 'active' : 'pending_approval',
```
Se admin ha manualmente approvato un membro con status `active`, questo torna `pending_approval` ogni volta che il membro modifica il profilo senza spuntare `onboarding_completed`.

**Soluzione:**
```php
// Preserva status esistente se già active
$currentStatus = $profile->status;
$newStatus = $request->boolean('onboarding_completed') 
    ? MemberProfileStatus::Active 
    : ($currentStatus === MemberProfileStatus::Active ? MemberProfileStatus::Active : MemberProfileStatus::PendingApproval);
```

### C6. File spurio nella root — DA ELIMINARE SUBITO
```
/kommunity/each(function
```
Questo file nella root del progetto è il risultato di un copy-paste accidentale di codice PHP nel terminale. Non causa errori in PHP ma è un indice di qualità del repository bassa e potrebbe creare problemi su alcuni server.

### C7. Validazione URI nei URL social
Il controller usa `preg_match` per aggiungere `https://`, ma la validation rule è `'nullable', 'string', 'max:255'` — non valida che sia effettivamente un URL valido. Aggiungere `'url:http,https'` oppure una regex custom.

### C8. Onboarding non protetto da middleware
Dopo la registrazione, l'utente accede immediatamente a directory, forum, events, messaggi — con `is_active = false` e profilo vuoto. Non esiste un middleware che forzi il completamento dell'onboarding.

**Soluzione:**
```php
// Middleware EnsureProfileComplete
public function handle(Request $request, Closure $next)
{
    $profile = $request->user()->memberProfile;
    if (!$profile?->onboarding_completed && !$request->routeIs('profile.*', 'locale.*', 'logout')) {
        return redirect()->route('profile.edit')
            ->with('info', 'Completa il profilo per accedere alla community.');
    }
    return $next($request);
}
```

---

## D. ANALISI FUNZIONALE DETTAGLIATA

### D1. Registrazione e accesso
**Funziona:** Registrazione con referral, email verification, ruolo `membro` auto-assegnato, observer che crea profilo + onepage.
**Manca:** 
- Campo "invitato da" è obbligatorio ma libero (testo). Non verifica che esista davvero.
- Se referral_code non corrisponde a nessun utente, il form silenziosamente procede senza pre-compilare.
- Nessuna email di benvenuto con istruzioni dopo la registrazione.
- Nessun double opt-in beyond email verification.

**Soluzione:** Aggiungere `MemberWelcomeNotification` nella `Registered` event listener (già esiste la notification, non è chiaro se venga inviata).

### D2. Profilo e onboarding
**Funziona:** Form completo con avatar, video, gallery, bio, professioni, categorie, città.
**Manca:**
- Wizard step-by-step (il form è uno solo, lunghissimo)
- Progress bar "profilo X% completato"
- Preview del proprio Onepage prima di salvare
- Nessuna guida su cosa inserire nei campi (tooltip, esempi)
- Il campo "Pianeta" è read-only → l'utente non sa come richiederne uno
- L'intro_video ha due opzioni (upload file + URL YouTube/Vimeo) nella stessa form senza spiegare la differenza chiaramente

### D3. Directory
**Funziona:** Filtri per categoria, regione, provincia, città, pianeta, ricerca testuale. Card con avatar, professione, video preview.
**Manca:**
- Nessun accesso senza abbonamento (la logica `hasDirectoryAccess()` esiste ma non è chiaro se applicata alla rotta `/directory`)
- Nessun sort alternativo (per data di iscrizione, per professione)
- Stato vuoto non progettato
- Cliccando su un membro si apre la sua Onepage — il flusso è corretto ma il ritorno alla directory non è immediato

### D4. One-to-One
**Funziona:** Richiesta con slot disponibilità, accettazione/rifiuto, note private, follow-up, completamento con doppia conferma.
**Manca:**
- Notifica email quando la richiesta viene accettata/rifiutata
- Vista calendario degli slot di disponibilità propri e altrui
- Impossibile riprenotare con lo stesso membro dopo il completamento senza nuovo flusso
- Il campo "orario richiesto" è libero, non forzato agli slot disponibili

### D5. Eventi
**Funziona:** Calendario mese/settimana/giorno/lista, iscrizione, disiscrizione, invito altri utenti, cancellazione evento.
**Manca:**
- Membro non può proporre un evento (solo admin/leader creano eventi)
- Nessun reminder automatico email prima dell'evento
- Il form di creazione evento è accessibile solo via modal nella vista calendario — non esiste una pagina dedicata
- Nessuna lista degli iscritti visibile al membro generico

### D6. Forum
**Funziona:** Thread, reply, category proposal, pin, lock (admin), ricerca.
**Manca:**
- Notifica email alla reply del proprio thread
- Nessun sistema di "like" o "utile"
- Nessun editor ricco (solo textarea)
- Thread non può essere modificato dopo la pubblicazione
- Nessun moderazione del contenuto oltre all'admin

### D7. Messaggi
**Funziona:** Conversazioni private 1:1, ricerca, filtro non letti.
**Manca:**
- Nessuna notifica email per nuovi messaggi
- Tutti i messaggi caricati senza paginazione (bug performance)
- Nessun indicatore di "online" o "letto"
- Non ci sono conversazioni di gruppo

### D8. Referral
**Funziona:** Creazione referral con priorità e valore stimato, aggiornamento status, filtri.
**Nota interessante:** I referral sono accessibili **solo tra utenti che hanno completato un 1:1** — meccanismo intelligente per garantire qualità delle segnalazioni.
**Manca:**
- Notifica email quando si riceve un referral
- Nessuna scadenza o follow-up automatico
- Nessuna dashboard aggregata del valore generato

### D9. Abbonamenti
**Funziona:** Vista piani, richiesta con metodo pagamento, cancellazione richiesta pendente.
**Manca:**
- Nessun payment gateway (Stripe, PayPal) — tutto manuale
- Admin deve approvare manualmente ogni richiesta
- Nessuna email di conferma automatica all'approvazione
- Nessun rinnovo automatico
- Nessun portale cliente per gestire i pagamenti

### D10. Admin Panel (Filament)
**Funziona:** CRUD completo per tutte le entità, gestione ruoli e permessi, SiteSettings, PageResource.
**Manca:**
- Dashboard admin senza widget (cartella Widgets è vuota)
- Nessun grafico KPI (iscrizioni nel tempo, conversazioni attive, etc.)
- Nessun alert per abbonamenti in scadenza
- Nessun log di attività utenti

---

## E. ANALISI UX/UI DETTAGLIATA

### E1. Incoerenza visiva globale — CRITICO

Il prodotto ha **almeno 4 design system diversi** conviventi:

| Sezione | Tema | Palette |
|---------|------|---------|
| Homepage | Dark navy + verde oliva + teal grigio | `#07111a`, `--brand4: #8fcf7d` |
| Dashboard | Dark navy + verde brillante + teal | `#020b12`, `--km-green: #8BC53F` |
| Forum | Dark blue-green | `#001821`, `--kf-green: #79c843` |
| Profilo / Directory | Bianco / stone (chiaro) | Tailwind stone palette |
| One-to-One | Dark navy (simile dashboard) | Variabili km-* |

Un utente che naviga dalla homepage al forum sente di essere in un'app diversa. Il colore brand cambia dal verde oliva (`#55794F`) al verde brillante (`#8BC53F`) al verde lime (`#79c843`) senza motivazione.

**Soluzione urgente:** Definire UN palette ufficiale con 3 colori primari e implementarlo ovunque via Tailwind config:
```js
// tailwind.config.js
colors: {
  brand: { DEFAULT: '#537D4D', light: '#8BC53F', dark: '#3D5C38' },
  surface: { DEFAULT: '#020B12', 2: '#031822', 3: '#052532' },
  teal: { DEFAULT: '#2DD4BF' }
}
```

### E2. Homepage — BUONA

La landing page è visivamente moderna e ben strutturata: hero con network SVG animato, steps, features grid, testimonials, mappa Italia, footer completo. I testi sono chiari e orientati al beneficio.

**Problemi:**
- I testimonials sono placeholder (nomi e avatar finti: Francesca R., Alessandro T., Giulia M.)
- Le statistiche ("500+ professionisti", "20+ Pianeti") sono hardcoded nella view, non vengono dal DB
- Il menu hamburger mobile ha un'implementazione JavaScript inline non accessibile
- La sezione "contatti" è nel menu ma non esiste come sezione nella pagina (link `#contatti` non punta a nulla)

### E3. Dashboard — BUONA, perfettibile

La dashboard ha un layout moderno con card dark e glassmorphism. Mostra: eventi imminenti, 1:1 ricevuti, referral inviati, thread recenti, messaggi recenti.

**Problemi:**
- Non mostra lo stato del profilo (% completamento, profilo approvato/non approvato)
- Non mostra notifiche in-app
- Il "benvenuto" è generico, senza personalizzazione reale
- Mobile: i card si accumulano verticalmente senza priorità chiara

### E4. Profilo — DA RIVEDERE

Il profilo è un form unico lunghissimo con sezioni: identità, profilo business, bio, video, contatti, gallery. Non ha step o wizard. Non ha preview.

**Problemi critici:**
- Nessuna indicazione di obbligatorietà visiva chiara (gli asterischi ci sono ma piccoli)
- Il campo "Pianeta" è read-only con messaggio "L'admin ti assegnerà" — l'utente non sa quando/come
- Il video ha due modalità (upload + URL) senza spiegazione chiara della differenza
- Nessun button "Salva bozza" — salva sempre e completamente
- Nessun feedback visivo di sezione salvata (solo flash di successo in cima)

### E5. Directory — BUONA

La directory ha card con avatar che fuoriesce dalla card, video preview sull'hover, filtri multipli.

**Problemi:**
- L'avatar che fuoriesce richiede CSS con `position: absolute; transform: translate(-50%, -50%)` — può rompersi su layout diversi
- Non esiste un indicatore di "disponibile per 1:1"
- La paginazione è presente ma non mostra il totale dei risultati
- Su mobile la sidebar dei filtri non è accessibile (non c'è un trigger per aprirla)

### E6. Forum — DISCRETO

Layout a 3 colonne (categorie | thread | featured). Ha statistiche in header, filtri base.

**Problemi:**
- L'header del forum con statistiche usa valori parzialmente calcolati lato view con operazioni matematiche strane (`(($thread->posts_count ?? 0) * 31) + ($thread->id % 17)` per simulare "visite" — questo è un fake-metrics evidente)
- Le categorie hanno icone definite tramite regex sul nome (`'collaborazioni' => [...]`) — fragile e non scalabile
- Nessun paginatore visibile nella lista thread
- La sidebar "Featured threads" ricarica gli stessi thread del main

### E7. Messaggi — SUFFICIENTE

Layout a due colonne: lista conversazioni | chat aperta.

**Problemi:**
- Nessun auto-scroll al fondo della chat (l'utente deve scrollare manualmente)
- Nessun indicatore di "messaggio inviato / consegnato"
- Su mobile la transizione tra lista e chat non è fluida

### E8. One-to-One — BUONA

L'interfaccia one-to-one è la più elaborata: ha filtri, statistiche, dettaglio laterale, gestione slot disponibilità, note private, follow-up. 

**Problemi:**
- Troppo denso per un utente non tecnico. Molte opzioni visibili simultaneamente.
- Il concetto di "slot di disponibilità" è tecnico e non immediato
- Il flusso di prenotazione automatica vs manuale non è spiegato chiaramente all'utente

---

## F. ANALISI TESTI E MICROCOPY

### F1. Testi da correggere/migliorare

| Testo attuale | Problema | Testo suggerito |
|--------------|----------|-----------------|
| `"Questo spazio verrà popolato con la presentazione professionale del membro."` | Testo tecnico auto-generato visibile agli utenti | `"Presentati alla community — aggiungi la tua bio."` |
| `"Kommunity genera automaticamente un mini sito personale per ogni iscritto."` | Testo interno auto-generato che diventa pubblico | Personalizzare nel profilo o nascondere finché non compilato |
| `"Onboarding membro"` come titolo della pagina profilo | Non orientato all'utente, suona burocratico | `"Il tuo profilo professionale"` |
| `"Completa il profilo business e le preferenze di networking"` | Lungo, freddo | `"Presentati alla community"` |
| `"Iscrizione automatica in lista d'attesa: Pianeta al completo per questa professione."` | Messaggio di errore tecnico esposto all'utente | `"Il Pianeta è al completo per la tua professione. Ti abbiamo messo in lista d'attesa — ti avviseremo appena si libera un posto."` |
| `"Nuovo membro della kommunity Kommunity."` | Errore: "kommunity" in minuscolo + ripetizione del brand | Rimuovere questa short_bio default o personalizzarla |
| `"Profilo professionale in costruzione"` | Visibile nel mini-sito pubblico | Nascondere il mini-sito finché il profilo non è completato |
| Bottone `"Registrati"` | Generico | `"Crea il mio account"` |
| `"Invitato da *"` obbligatorio | Frena la registrazione spontanea | Rendere facoltativo con spiegazione |
| Errore 403: pagina generica | Non spiega cosa fare | `"Non hai i permessi per questa pagina. Se pensi sia un errore, contattaci."` |

### F2. Accentazione italiana
In vari punti della codebase si trovano parole con apostrofo al posto dell'accento (`"komunità"`, `"attività"`, `"Pianeta"` con la a accentata mancante). Fare un audit completo dei testi.

### F3. Lingua mista
Il sistema è impostato per italiano/inglese con file di lingua in `lang/it/` e `lang/en/`, ma molte stringhe nei template sono hardcoded in italiano direttamente nelle view, bypassando il sistema di localizzazione. Qualsiasi cambio lingua parziale risulterà inconsistente.

---

## G. ANALISI SICUREZZA

### G1. Configurazione — OK per produzione se ben configurato
```
APP_DEBUG=false         ✓ (in .env.production.example)
APP_ENV=production      ✓
SESSION_SECURE_COOKIE=true  ✓
SESSION_ENCRYPT=true    ✓
LOG_LEVEL=warning       ✓
```
Attenzione: `.env.example` ha ancora `APP_DEBUG=false` ma `LOG_LEVEL=debug` — rischio se copiato direttamente.

### G2. CSRF — OK
Tutte le form hanno `@csrf`. Le route POST/PATCH/DELETE sono protette dal middleware CSRF di default.

### G3. XSS — RISCHIO MEDIO
- Blade usa `{{ }}` per output escaped — OK per la maggior parte.
- La route `PageController` potrebbe mostrare contenuto CMS con `{!! $body !!}` (unescaped) — visibile nella view register. Verificare che il campo `body` delle Page sia sanitizzato lato admin.
- Il forum thread mostra contenuto utente — verificare che `{{ }}` sia sempre usato e mai `{!! !!}`.

### G4. SQL Injection — OK
Eloquent con query builder protegge da SQL injection. Non ci sono query raw pericolose evidenti.

### G5. Upload file — RISCHIO MEDIO
- Avatar/logo/cover: `'image'` validation + `max:4096` ✓
- Video: `mimetypes:video/mp4,video/quicktime,video/webm` — MIME type si può falsificare. Aggiungere anche `max` e, se possibile, verifica dell'intestazione reale del file con `finfo`.
- Gallery: `max:12` immagini ✓
- Non è chiaro se i file vengono serviti attraverso `MediaController` (con controllo auth) o direttamente da `public/storage` (accessibili a tutti). Verificare il `storage:link` e la configurazione `FILESYSTEM_DISK`.

### G6. Rate limiting — INSUFFICIENTE
Solo le route auth hanno `throttle:6,1`. Le route di forum, messaggi, referral non hanno rate limiting → rischio flood di contenuti.

**Aggiungere:**
```php
Route::middleware(['auth', 'verified', 'throttle:60,1'])->group(function () {
    Route::post('/forum/{thread}/reply', ...);
    Route::post('/conversations/{conversation}/messages', ...);
    Route::post('/referenze', ...);
});
```

### G7. Password — OK
`Rules\Password::defaults()` con BCRYPT_ROUNDS=12. Buono.

### G8. Permessi Filament
`canAccessPanel()` nel User model verifica ruoli E permessi. Ok. Ma non esiste un controllo sulla pagina SiteSettings che verifica se l'utente sia super-admin — chiunque con accesso al pannello Filament potrebbe modificare le impostazioni globali.

### G9. Dati personali (GDPR)
- Esiste `profile.destroy` che cancella l'account
- Non c'è una pagina "Esporta i miei dati"
- Non c'è una Privacy Policy/Cookie Policy nel sistema (potrebbero essere come CMS Pages ma non verificabile senza dati)
- Il numero di WhatsApp viene passato al frontend per generare il link `wa.me/` — OK se l'utente ha abilitato `show_whatsapp`

---

## H. ANALISI PERFORMANCE

### H1. Query critiche

| Controller | Problema | Impatto |
|-----------|---------|---------|
| `ConversationController::conversationList()` | Carica TUTTE le conversazioni + TUTTI i messaggi senza paginazione | CRITICO |
| `DirectoryController::__invoke()` | `inRandomOrder()` full table scan | ALTO |
| `EventController::index()` | Molteplici query separate con `withCount` annidato (×4 per ogni query) | MEDIO |
| `OneToOneController::index()` | `User::query()->whereHas(...)->get()` carica TUTTI i membri per dropdown | MEDIO |
| `ReferralController::index()` | `User::query()->whereIn($eligibleMemberIds)->get()` carica tutti gli utenti idonei | MEDIO |
| `layouts/navigation.blade.php` | Query DB ad ogni page load senza cache | ALTO |
| `auth/register.blade.php` | 3 query `SiteSetting::get()` per ogni visualizzazione | MEDIO |

### H2. Asset e build
- Vite è configurato correttamente con `resources/css/app.css` e `resources/js/app.js`
- Il build è presente in `public/build/` con `manifest.json`
- Il CSS inline nelle view bypassa completamente il vantaggio della build Vite
- Non c'è lazy loading delle immagini nella directory (con 12 card × avatar + copertina = 24 immagini caricate subito)

### H3. Cache Laravel
Non c'è utilizzo di `Cache::remember()` in nessun controller. In produzione su cPanel con `CACHE_STORE=database`, ogni query non cachata colpisce il DB.

**Quick wins:**
```php
// In AppServiceProvider::boot()
View::composer('layouts.navigation', function ($view) {
    $view->with('hasActivePlans', Cache::remember('has_active_plans', 3600, 
        fn() => SubscriptionPlan::where('is_active', true)->exists()
    ));
});
```

### H4. Immagini
- Nessun resize automatico delle immagini al caricamento (avatar da 4MB rimane 4MB)
- Nessun WebP conversion
- Nessun lazy loading attributo `loading="lazy"` nelle card directory
- Il `VideoCompressor` usa ffmpeg che probabilmente non è disponibile su hosting condiviso cPanel → i video rimangono nella dimensione originale

### H5. Queue
Il `QUEUE_CONNECTION=database` è configurato ma non è chiaro se le notifiche email usino la queue. Su hosting condiviso, i job sincroni bloccano le richieste HTTP.

---

## I. MIGLIORAMENTI IN ORDINE DI PRIORITÀ

### INDISPENSABILI PRIMA DEL LANCIO

1. **Eliminare file spurio** `each(function` dalla root
2. **Creare middleware onboarding** che blocchi l'accesso alle sezioni fino al completamento del profilo
3. **Notifiche email minime:** 1:1 ricevuto/accettato/rifiutato, nuovo messaggio, abbonamento approvato
4. **Unificare il design system** con un'unica palette CSS e rimuovere gli stili inline dalle viste
5. **Paginare i messaggi** nella ConversationController
6. **Correggere status hardcoded** nel ProfileController (`is_visible_in_directory: true` e status override)
7. **Rate limiting** su POST di messaggi, forum, referral
8. **Eliminare i 7 file di backup** dalle views
9. **Correggere i testi** auto-generati che diventano pubblici nel mini-sito
10. **Correggere `#contatti`** nel menu homepage (link broken)

### UTILI DOPO IL LANCIO

11. **Onboarding wizard** step-by-step con progress bar
12. **Creare Policy** per i modelli principali
13. **Cachare** le query ripetute (nav, SiteSettings, directory order)
14. **Dashboard admin** con widget KPI (iscrizioni, abbonamenti, conversazioni)
15. **Notifiche in-app** (badge nel menu + pagina notifiche)
16. **Empty state pages** per tutte le sezioni
17. **Preview del proprio Onepage** dalla pagina profilo
18. **Resize automatico immagini** al caricamento (max 800px width per avatar)
19. **Statistiche reali** nella homepage (da DB, non hardcoded)
20. **Separare EventController::index()** in metodi più piccoli

### EVOLUZIONI FUTURE

21. **Payment gateway** (Stripe o PayPal) per abbonamenti automatici
22. **Notifiche push browser** per nuovi messaggi
23. **Editor ricco** per forum (markdown o WYSIWYG)
24. **API REST** per app mobile
25. **Ricerca globale** (Meilisearch o Typesense)
26. **Analytics di engagement** per admin (chi usa cosa, chi è inattivo)
27. **Proposte di connessione automatiche** basate su professione/interessi
28. **Integrazione calendario** (Google Calendar, iCal) per eventi e 1:1
29. **Sistema di badge e gamification**
30. **White-label** per vendere il sistema ad altre community

---

## J. ROADMAP CONSIGLIATA

### Interventi urgenti (questa settimana)
- [ ] Eliminare `each(function` dalla root del progetto
- [ ] Eliminare i 7 file `.bladeOLD` / `backup` / `Copia`
- [ ] Fix `ProfileController::update()` → status e `is_visible_in_directory`
- [ ] Fix testi auto-generati nel mini-sito (short_bio, about_text, services_text)
- [ ] Fix link `#contatti` nella homepage
- [ ] Aggiungere rate limiting alle route POST critiche
- [ ] Verificare che `APP_DEBUG=false` in produzione

### Interventi entro 7 giorni
- [ ] Middleware `EnsureProfileComplete` per onboarding
- [ ] Notifiche email: 1:1 ricevuto/accettato, nuovo messaggio, abbonamento approvato
- [ ] Paginazione messaggi nel ConversationController
- [ ] Cache per la query nella navigation bar
- [ ] Fix statistiche homepage (renderle dinamiche o rimuoverle)
- [ ] Test manuale di tutti i flussi principali su browser mobile

### Interventi entro 30 giorni
- [ ] Unificazione CSS/design system in una palette unica
- [ ] Wizard onboarding step-by-step
- [ ] Policy di autorizzazione per i model principali
- [ ] Widget dashboard Filament (KPI admin)
- [ ] Empty state per tutte le sezioni
- [ ] Ottimizzare le query principali (ConversationController, DirectoryController)
- [ ] Lazy loading immagini nelle card directory
- [ ] Audit completo testi con correzione accentuazioni e inconsistenze

### Evoluzioni future (oltre 30 giorni)
- [ ] Integrazione payment gateway (Stripe)
- [ ] Notifiche in-app con badge nel menu
- [ ] Editor ricco per il forum
- [ ] Analytics utenti per admin
- [ ] API per eventuale app mobile

---

## K. CHECKLIST PRE-LANCIO

### Ambiente e configurazione
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL` con HTTPS
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `SESSION_ENCRYPT=true`
- [ ] `LOG_LEVEL=warning`
- [ ] `MAIL_MAILER=smtp` con dati reali
- [ ] Database MySQL configurato (non SQLite)
- [ ] `php artisan storage:link` eseguito
- [ ] `php artisan optimize` eseguito
- [ ] `php artisan migrate --force` eseguito

### Codice
- [ ] File spurio `each(function` eliminato
- [ ] File backup/OLD nelle views eliminati
- [ ] `ProfileController::update()` corretto (status e visibilità)
- [ ] Testi auto-generati nel mini-sito corretti o nascosti
- [ ] Rate limiting aggiunto alle route POST

### Funzionale
- [ ] Registrazione completa funzionante (con e senza referral)
- [ ] Email verification funzionante
- [ ] Profilo salvato correttamente con immagini
- [ ] Directory mostra i membri attivi
- [ ] Evento creabile da admin e visibile ai membri
- [ ] 1:1 inviabile e ricevibile
- [ ] Messaggi funzionanti
- [ ] Abbonamento richiedibile
- [ ] Admin panel accessibile solo ad admin

### Sicurezza
- [ ] Upload file testati con file malevoli (nome con script, tipo MIME falso)
- [ ] Form CSRF verificato
- [ ] Route `/admin` inaccessibile senza ruolo
- [ ] Profilo di un altro utente non modificabile

### UX
- [ ] Test su mobile (iPhone + Android)
- [ ] Test su tablet
- [ ] Tutti i link funzionanti (specialmente `#contatti`)
- [ ] Empty state presenti per le sezioni principali
- [ ] Messaggi di errore chiari e in italiano
- [ ] Flash message di successo visibili dopo ogni azione

### Email
- [ ] Email di verifica inviata e ricevuta
- [ ] Email reset password funzionante
- [ ] (Se attivato) Email di benvenuto funzionante
- [ ] From address con dominio reale (non `noreply@kommunity.test`)

---

## L. SUGGERIMENTI CONCRETI

### L1. Middleware onboarding (codice)
```php
// app/Http/Middleware/EnsureProfileComplete.php
public function handle(Request $request, Closure $next): Response
{
    $user = $request->user();
    if ($user && !$user->memberProfile?->onboarding_completed) {
        $allowed = ['profile.*', 'locale.*', 'logout', 'verification.*'];
        if (!$request->routeIs(...$allowed)) {
            return redirect()->route('profile.edit')
                ->with('warning', 'Completa il tuo profilo per accedere alla community.');
        }
    }
    return $next($request);
}
```

### L2. Cache navigation query
```php
// app/View/Composers/NavigationComposer.php
class NavigationComposer {
    public function compose(View $view): void {
        $view->with('hasActiveSubscriptionPlans', 
            Cache::remember('nav.has_plans', 3600, 
                fn() => SubscriptionPlan::where('is_active', true)->exists()
            )
        );
    }
}
// In AppServiceProvider::boot():
View::composer('layouts.navigation', NavigationComposer::class);
```

### L3. Conversazioni: caricare solo l'ultimo messaggio
```php
// In Conversation model
public function scopeWithLastMessage($query) {
    return $query->addSelect([
        'last_message_id' => Message::select('id')
            ->whereColumn('conversation_id', 'conversations.id')
            ->latest()
            ->limit(1)
    ])->with('lastMessage.user');
}
```

### L4. Rate limiting (web.php)
```php
Route::middleware(['auth', 'verified', 'throttle:30,1'])->group(function () {
    Route::post('/forum/{thread}/reply', [ForumController::class, 'reply']);
    Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'storeMessage']);
    Route::post('/referenze', [ReferralController::class, 'store']);
    Route::post('/one-to-one', [OneToOneController::class, 'store']);
});
```

### L5. Fix status nel ProfileController
```php
// Preserva lo status attivo se già approvato dall'admin
$currentProfile = $request->user()->memberProfile()->first();
$newStatus = match(true) {
    $request->boolean('onboarding_completed') => MemberProfileStatus::Active,
    $currentProfile?->status === MemberProfileStatus::Active => MemberProfileStatus::Active,
    default => MemberProfileStatus::PendingApproval,
};
```

---

## VOTI FINALI

| Dimensione | Voto | Note |
|-----------|------|------|
| **Tecnico** | **6/10** | Struttura Laravel buona, ma CSS frammentato, no Policy, query non ottimizzate, file spurio |
| **UX/UI** | **5/10** | Design moderno ma 4 sistemi visivi diversi, no onboarding, no empty state, incoerenza grave |
| **Funzionale** | **6.5/10** | Funzionalità core tutte presenti, ma mancano notifiche email, payment gateway, onboarding guidato |
| **Prontezza al lancio** | **4/10** | Non pronto. Troppi problemi bloccanti per un cliente pagante |

---

## LE 10 COSE PIÙ IMPORTANTI DA SISTEMARE SUBITO

1. **Eliminare `each(function`** dalla root del progetto (30 secondi)
2. **Fix ProfileController** — rimuovere `is_visible_in_directory: true` hardcoded e il status override
3. **Middleware onboarding** — bloccare l'accesso alle sezioni senza profilo completato
4. **Notifiche email minime** — almeno: 1:1 ricevuto, messaggio ricevuto, abbonamento approvato
5. **Paginare i messaggi** nel ConversationController — altrimenti crash in produzione
6. **Unificare il CSS** — almeno allineare le variabili di colore tra le sezioni
7. **Rate limiting** sulle route POST di messaggi, forum e referral
8. **Eliminare i file backup** dalle views (`bladeOLD`, `Copia`, `backup`)
9. **Correggere i testi auto-generati** che appaiono nel mini-sito pubblico
10. **Cache per la query nella navbar** e per i SiteSetting nella registrazione

---

## LE 10 FUNZIONI CHE RENDEREBBERO IL SISTEMA PIÙ VENDIBILE

1. **Onboarding wizard** con progress bar e step guidati — aumenta la completion rate
2. **Notifiche in-app + email** per ogni interazione — fa sentire il sistema "vivo"
3. **Integrazione Stripe** per abbonamenti automatici — rimuove la dipendenza dall'admin
4. **Dashboard analytics per l'admin** — KPI chiari aumentano la fiducia del cliente
5. **Ricerca globale** (membri + thread + eventi in un campo unico)
6. **Badge "Disponibile per 1:1"** sulla card del membro in directory
7. **Reminder automatico** prima degli eventi (email 24h prima)
8. **"Profilo X% completato"** con suggerimenti su cosa aggiungere
9. **Proposte di connessione automatiche** ("Potresti conoscere: X — stesso settore, stessa città")
10. **Esporta contatti** — lista dei propri 1:1 completati e referral come CSV

---

*Fine del report — Kommunity Analysis v1.0 — 2026-04-29*
