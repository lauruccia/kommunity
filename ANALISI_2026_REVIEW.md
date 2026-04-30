# ANALISI APPROFONDITA — KOMMUNITY (rev. 29 aprile 2026)

> Audit indipendente: senior Laravel dev + product manager + UX designer + consulente SaaS + QA + esperto conversione.
> Documento parallelo a `ANALISI_COMPLETA.md` esistente; lo aggiorna, lo corregge e lo amplia. Nessun file del progetto modificato.

---

## A. SINTESI GENERALE

### A.1 Cosa fa il sistema
Kommunity è una **piattaforma SaaS di networking professionale verticale sull'Italia**, ispirata al modello BNI/Networking Pro, costruita su Laravel 12 + Filament 4. Permette ai membri di:

- creare un profilo professionale completo (azienda, professione, settore, città, video, gallery);
- ottenere automaticamente una "onepage" (mini-sito personale pubblico/privato) sotto `/member/{slug}`;
- entrare in **Pianeti** (chapters territoriali/tematici) con limite massimo di membri per professione e lista d'attesa automatica;
- prenotare incontri **One-to-One** con altri membri tramite slot di disponibilità settimanali;
- partecipare ad **eventi** del proprio Pianeta con sistema di inviti segmentati (per chapter, professione, città, regione, lista personalizzata);
- usare un **forum** con categorie, thread, risposte annidate, proposte di nuove categorie;
- scambiarsi **messaggi privati** 1:1;
- inviare/ricevere **referral business** tracciati con priorità, valore stimato e stato (gating: solo dopo un One-to-One completato e confermato dalle due parti);
- sottoscrivere **abbonamenti** a piani configurabili con pagamento manuale (bonifico/carta/PayPal) approvato dall'admin;
- gestire un **referral program** integrato (codice referral personale generato automaticamente).

L'admin gestisce tutto via Filament 4: utenti, ruoli, capitoli, professioni, categorie, città, eventi, conversazioni, referral, piani, pagine CMS, site settings, suggerimenti membri.

### A.2 Stack rilevato
- PHP 8.2 + Laravel 12.56
- Filament 4 (admin), Spatie Permission, Laravel Breeze (auth)
- Blade + Alpine.js + Tailwind 3 + Vite 7
- SQLite in dev, MySQL/MariaDB in produzione (target cPanel hosting condiviso, niente SSH)
- Niente queue worker (queue su `database` ma nessun worker dichiarato), niente Redis, niente WebSocket, niente payment gateway reale, niente API REST.

### A.3 Livello di maturità
**Prototipo avanzato / beta privata (non production-ready).**

Il dominio funzionale è impressionante per ampiezza: 33 modelli, 23 migrations, 27 controller, 27 risorse Filament, oltre 50 viste Blade, sistema di permessi, onboarding wizard, notifiche in-app, multilingua (it/en). Per contro:

- nessuna **Policy** Laravel esiste (cartella `app/Policies/` assente);
- 17 file `.bak` lasciati nel repository, anche dentro `resources/views`, `routes/`, `app/Notifications`, `app/Enums` (artefatti di lavorazione);
- **CSS inline in 18 viste**, con palette di colori e variabili CSS che cambiano da pagina a pagina;
- **APP_DEBUG=false ma APP_ENV=local e APP_URL=http://localhost** in `.env`: configurazione mista non destinabile né allo sviluppo né alla produzione;
- testi UI parlano in modo hardcoded di "Pianeta Roma / Lazio" anche per utenti che non sono in nessun chapter;
- nessuna policy `gate` per l'accesso al pannello Filament differenziato (controllo via `hasAnyRole` cablato nel modello User).

### A.4 Giudizio complessivo
Il progetto ha **ambizione e copertura funzionale notevoli** e una buona parte del lavoro "noioso" (relazioni, migrazioni, observer, enum, multilingua, ruoli) è già stata fatta in modo corretto. Però il livello di rifinitura è quello di un MVP interno, non di un prodotto consegnabile a un cliente pagante. Tre problemi strutturali tirano giù la qualità:

1. **Layer di sicurezza/autorizzazione fragile** (zero policy, controlli sparsi, 16 `abort_unless` totali).
2. **CSS architetturalmente sbagliato** (inline in ogni vista → impossibile garantire coerenza visiva, branding, manutenzione).
3. **Esperienza utente disomogenea**: registrazione "card chiara", onboarding "modal scuro Roma", dashboard "scuro Roma hardcoded", directory "card chiara", forum "scuro teal", messaggi "scuro green". Sembrano sei prodotti diversi.

### A.5 Pronto per il primo cliente?
**No.** Servono almeno 2-3 settimane di lavoro mirato prima di poter consegnare in produzione anche solo a un cliente pilota tollerante. Il punto bloccante non è "manca una feature" — sono tante piccole cose la cui somma genera sfiducia (debug, errori 500, link rotti, pulsanti che cambiano colore tra pagine).

### A.6 Principali rischi
1. **APP_KEY committata in `.env`** (presente nel repo del workspace). Va rigenerata e tenuta fuori da git in produzione.
2. **MediaController accetta path arbitrari** — Laravel previene path traversal, ma non c'è un test esplicito.
3. **Caricamento file utente non validato come tipo MIME reale** in alcuni punti (solo `image` e `mimetypes` su intro_video).
4. **Onboarding "morbido"**: il middleware EnsureOnboardingComplete redirige ma non blocca le route Filament o l'editing del profilo, quindi un utente può saltare e accedere comunque ad alcune aree.
5. **Notifiche solo in-app**: nessuna email su nuovi messaggi, referral, eventi. L'utente che riceve un invito non riceve email → l'app sembra morta.
6. **Pagamenti manuali**: l'admin deve approvare ogni abbonamento. Non scalabile se cresci oltre i primi 30-50 paganti.
7. **Hosting cPanel condiviso senza SSH**: deploy/aggiornamenti complicati. Va prevista una procedura precisa per far girare migration e cache:clear da artisan via webroute o pannello.

---

## B. TABELLA PROBLEMI TROVATI

| # | Area | File / Schermata | Problema | Gravità | Impatto | Soluzione consigliata |
|---|------|------------------|----------|---------|---------|-----------------------|
| 1 | Configurazione | `.env` | `APP_ENV=local` con `APP_URL=http://localhost` ma `APP_DEBUG=false`: stato ibrido. APP_KEY committata. | ALTA | In dev gli error page sono opachi; in prod il link generato sarà localhost. | Allineare ad ambiente reale. APP_KEY fuori da git. Usare `.env.example` "vero". |
| 2 | Sicurezza | `app/` | **Zero Policy**. Autorizzazioni basate su 16 `abort_unless` sparsi nei controller. | ALTA | Logica autorizzazione duplicata, difficile da auditare, facile bug di "accesso a risorsa non propria". | Creare `EventPolicy`, `OneToOnePolicy`, `ConversationPolicy`, `ReferralPolicy`, `MemberOnepagePolicy`. |
| 3 | Codice | tutto progetto | **17 file `.bak` committati**: views, routes, notifications, enums. | ALTA | Confusione, rischio di caricarli per errore in produzione, repository sporco. | Rimuovere tutti i `.bak` e aggiungere `*.bak` al `.gitignore`. |
| 4 | UX/Brand | Dashboard | Hardcoded "Pianeta Roma / Lazio" in stringhe e CTA, anche se l'utente non è in nessun chapter. | ALTA | L'utente di Milano vede "Pianeta Roma" in dashboard → percezione di prodotto rotto/abbandonato. | Sostituire con `{{ $user->memberProfile?->chapter?->name ?? 'Nessun Pianeta' }}`. Nascondere riferimenti localizzati se profilo vuoto. |
| 5 | Architettura CSS | 18 viste | `<style>` inline con palette diversa per ogni schermata; variabili `--km-*` ridefinite ovunque. | ALTA | Manutenzione impossibile, tema incoerente, browser carica CSS duplicato. | Estrarre tutto in `resources/css/app.css` con un design system unico (1 palette, 1 tipografia, 1 spaziatura). |
| 6 | Performance | `ConversationController::index` | Carica tutte le conversazioni dell'utente con `with('messages.user')` e nessuna paginazione. | ALTA | Con 100 conversazioni e 50 messaggi a testa: 5.000 record in memoria a ogni page load. | Paginare, caricare solo l'ultimo messaggio (`with(['lastMessage'])` via `HasOne` ordered). |
| 7 | Performance | `DashboardController` | 5 query separate caricate sempre, una di queste fa `whereIn('conversation_id', $ids)` con pluck di tutti gli ID. | MEDIA | OK con 5 conversazioni, lento con 200. | Aggiungere indice su `conversation_id` (già presente?), unire query con `selectRaw` o cachare per 60s. |
| 8 | Performance | `DirectoryController` | `inRandomOrder()` su `MemberProfile` su MySQL = `ORDER BY RAND()` → table scan. | ALTA | Con 5.000 membri il listing della directory diventa lento (>2s). | Sostituire con random a livello applicativo (`->get()->shuffle()->take($n)`) o cachare un seed orario. |
| 9 | Performance | `EventController::index` | Calcola 4 query "futureRegisteredEvent / futureInvitedEvent / nextFutureEvent" anche quando non servono. | MEDIA | Latenza extra a ogni apertura calendario eventi. | Calcolare lazy solo se nessuna data forzata. |
| 10 | Sicurezza | `MediaController` | Route `/media/{path}` con `where('path', '.*')`: accetta qualsiasi path. | MEDIA | Laravel `Storage::disk('public')->exists($path)` previene il traversal, ma **manca header Cache-Control** e mime detection. | Validare con `Str::startsWith($path, ['members/','events/'])`, restituire `headers` con cache lunga. Meglio: usare il symlink `/storage` di Laravel direttamente (più veloce, niente hop PHP). |
| 11 | Sicurezza | `RegisteredUserController` | `referral_code` cercato in `User::where('referral_code', ...)` senza limite tentativi: enumeration di codici via brute. | BASSA | Permette di estrarre la lista codici referral. | `throttle:5,1` su POST register, log dei tentativi falliti. |
| 12 | Bug logico | `ProfileController::update` | `'is_active' => true` cablato → l'admin non può sospendere un membro perché lo riattiva al primo save. | ALTA | Sospensioni inefficaci. | Rimuovere la riga, gestire `is_active` solo via Filament. |
| 13 | Bug logico | `MemberProfile::booted` | `static::$adminOverrideLimit` flag globale statico → race condition possibile in queue/job. | MEDIA | Due salvataggi paralleli possono spegnere/accendere il flag in modo imprevisto. | Passare l'override come parametro esplicito o disabilitare via service container scoped. |
| 14 | Bug | `ProfileController::update` line 78-81 | `storePublicFile` ritorna path relativo ma `deletePublicImage` si aspetta URL completo. | MEDIA | Vecchi file non vengono cancellati → storage cresce indefinitamente. | Normalizzare: archivia sempre come path relativo, calcola URL solo in lettura. |
| 15 | Bug | `EventController` linea 141-144 | Carica `Profession::all()`, `Category::roots()`, `Region::all()`, `City::all()` su index per gli admin. | MEDIA | Con 8.000 città italiane: 8.000 record JSON serializzati ad ogni apertura calendario. | Caricare città solo on-demand via select2 / autocomplete AJAX. |
| 16 | UX | `welcome.blade.php` | Vista da 250+ righe con CSS interno, copy "Italia/Lazio" misto, network animato decorativo. | MEDIA | Carica > 60 KB di CSS inline a ogni visita home. | Estrarre CSS, dimezzare la lunghezza, mettere lazy-load sulla mappa Italia. |
| 17 | UX | Tutte le aree autenticate | Sei "temi" diversi: dashboard verde scuro, directory bianco, conversazioni teal, forum teal scuro, eventi cyan scuro, profile bianco. | ALTA | Sembra prodotti diversi. Distrugge la fiducia. | **Un solo tema** (consiglio: light premium con accent verde/teal) o **un solo dark theme**, non entrambi. |
| 18 | Funzionale | Onboarding wizard | Modal a 5 step con campi parziali (manca avatar, professione, città, video). | ALTA | Utente "completa l'onboarding" e arriva sulla directory comunque vuoto: profilo al 40%. | Aggiungere step "Foto e professione" obbligatori. Mostrare percentuale in tempo reale. |
| 19 | Funzionale | Notifiche | Solo notifiche in-app (DatabaseChannel). Mail mailer = log. | ALTA | Nessuna email reale per inviti, messaggi, referral, eventi. | Configurare SMTP reale, aggiungere `via: ['database', 'mail']` su tutte le notifiche. |
| 20 | Funzionale | `SubscriptionController` | Pagamenti manuali, no gateway. Admin approva manualmente. | MEDIA | Non scalabile oltre 30-50 paganti. | Integrazione Stripe Checkout o Mollie (entrambi supportano Italia + iva + IBAN). |
| 21 | Bug | `routes/web.php` | Notifiche route `/notifications/{id}/read` non sono dietro middleware `verified` (sono dentro il gruppo `verified`+`onboarding`). | BASSA | OK ma se utente non completa onboarding non può segnare letta una notifica → campanella sempre rossa. | Spostare le route notifiche fuori dal gruppo `onboarding`. |
| 22 | Bug | `ForumController::index` | `User::query()->count()` per "members" stat: conta TUTTI gli utenti registrati, anche admin/disabilitati. | BASSA | Numero gonfiato. | Filtrare per `is_active` e ruolo `membro`. |
| 23 | Performance | `layouts/navigation.blade.php` | 5 ultime notifiche caricate ad ogni page render anche se l'utente non apre la campanella. | BASSA | Query extra inutile. | Caricare on-demand al click su campanella (Alpine.js fetch). |
| 24 | UX | `ProfileUpdateRequest` | 30+ campi obbligatori in un'unica form (773 righe blade). | ALTA | Form-killer per non-tecnici. | Spezzare in 3 sezioni con tab + autosave. |
| 25 | Sicurezza | Upload | `mimetypes:video/mp4` su intro_video ok, ma immagini gallery solo `image` (no MIME check reale). | MEDIA | File con estensione modificata possono passare. | Aggiungere `mimes:jpg,jpeg,png,webp` esplicito. |
| 26 | Funzionale | `ReferralController` | Eligibilità basata su `OneToOneRequest::completed`. Senza One-to-One, nessuno può inviare referral. | MEDIA | All'avvio, prima che ci siano 1:1, NESSUNO può fare referral → la feature sembra rotta. | Per i primi 30 giorni aprire i referral senza vincolo, oppure spiegare il vincolo in UI con CTA "Fai prima un 1:1". |
| 27 | UX | `members/show.blade.php` | 414 righe, mescola dati profilo e dati relazione (1:1 condivisi, referral condivisi). | MEDIA | Pagina lunga, scroll infinito, info sensibili miste. | Tab "Profilo / La nostra storia". Mostrare 1:1 e referral solo se c'è almeno 1. |
| 28 | UX | Conversazioni | Layout 3 colonne fisso 470+1fr+440 px → su laptop 13" il messaggio diventa illeggibile. | MEDIA | Esperienza scarsa su schermi <1400 px. | Layout responsive con dettaglio collassabile + breakpoint. |
| 29 | UX | Eventi | Calendario con 4 viste (mese/sett/giorno/lista) + tre pannelli laterali = sovraccarico. | MEDIA | L'utente non capisce dove cliccare. | Default solo "lista prossimi" + toggle calendario. |
| 30 | UX | Forum | Sidebar 300+1fr+390 px = grid molto stretta sui contenuti. | MEDIA | Thread illeggibili su laptop. | Layout 240+1fr, sidebar destra solo desktop XL. |
| 31 | Codice | `ProfileController::update` | Logica di normalizzazione URL ripetuta 5 volte con regex inline. | BASSA | Duplicazione. | Estrarre `Str::macro('toAbsoluteUrl', …)` o trait `NormalizesUrls`. |
| 32 | Codice | `OneToOneController::index` | 175 righe in un solo metodo, 3 `clone $statsQuery`, query annidate. | MEDIA | Difficile da leggere/testare. | Estrarre Service `OneToOneListing` con metodi `forUser()`, `summary()`. |
| 33 | Codice | `EventController::index` | 295 righe in un metodo. | MEDIA | Idem. | Estrarre `EventCalendar` service. |
| 34 | Test | `tests/Feature/` | Solo test di scaffold (auth Breeze, profile, eventi base). Nessun test su 1:1, referral, forum, conversazioni, abbonamenti. | ALTA | Refactor senza rete. | Aggiungere test feature per ogni controller principale. |
| 35 | Sicurezza | `routes/web.php` | Route `/admin/cache` (cache:clear) solo dietro `auth+verified`, **senza check ruolo**. | ALTA | Qualsiasi utente verificato può chiamare clear cache. | `->middleware('role:super-admin\|admin-community')`. |
| 36 | Bug | `EnsureOnboardingComplete` | Non filtra POST/AJAX → un fetch JSON ottiene redirect HTML, errore lato JS. | MEDIA | Errori JS silenziosi. | Rispondere 403 JSON se request `expectsJson()`. |
| 37 | UX | Toast/flash | Layout custom in `app.blade.php` ma errori validazione restano dentro `<x-input-error>` separati → due sistemi di feedback paralleli. | BASSA | Inconsistenza UX. | Unificare con un componente toast globale. |
| 38 | Performance | `MemberOnepageController::show` | Eager load 8 relazioni anche se l'utente guarda solo il tab "profilo". | BASSA | Latenza extra. | Lazy load per tab. |
| 39 | Funzionale | `Page` (CMS) | Pagine con HTML libero (`{!! $body !!}` in register.blade) → admin può inserire JS arbitrario. | ALTA | XSS stored se admin compromesso. | Sanitizer (HTMLPurifier) o whitelist tag. |
| 40 | Funzionale | Email verification | Su `.env`: `MAIL_MAILER=log` → l'utente non riceve mai l'email di verifica in dev. | MEDIA | Onboarding non testabile end-to-end. | Mailtrap o `mail` → file. |
| 41 | Funzionale | `welcome.blade.php` | Form newsletter senza conferma double opt-in, solo `POST /newsletter`. | MEDIA | Compliance GDPR fragile. | Double opt-in con email di conferma. |
| 42 | Funzionale | Cookie / GDPR | Nessuna cookie banner, nessuna privacy policy linkata. | ALTA | Impossibile lanciare in EU. | Aggiungere banner cookie + pagina `/privacy` + `/cookie-policy` + `/termini`. |
| 43 | Sicurezza | `vendor.zip` (18 MB) | File vendor.zip lasciato nel root del progetto. | MEDIA | Se esposto pubblicamente espone tutto vendor. | Eliminare. Aggiungere `vendor.zip` a `.gitignore`. |
| 44 | Codice | `User::canAccessPanel` | Logica complessa con lista hardcoded di ruoli e permessi. | BASSA | Aggiunta nuovo ruolo richiede modifica codice. | `Gate::define('access-admin', fn ($u) => $u->hasAnyRole([...]) )` o semplicemente `$user->can('access-admin')`. |
| 45 | UX | Dashboard widget completamento | "10 campi" totali ma il `ProfileCompletionService` ne ha 10 fissi → arbitrario. | BASSA | Utente non capisce cosa pesa cosa. | Aggiungere tooltip "perché serve". |

---

## C. ANALISI TECNICA DETTAGLIATA

### C.1 Struttura cartelle
La struttura segue la convenzione Laravel 12 standard. Aggiunte legittime:
- `app/Enums/` (11 enum tipizzati: ContactMethod, EventType, OneToOneStatus, ecc.) — **molto buono**, fa risparmiare bug.
- `app/Filament/` (27 risorse) — **completo**, c'è un panel admin per ogni concetto del dominio.
- `app/Services/` (1 file solo) — **sotto-utilizzato**, troppo lavoro nei controller. Andrebbero estratti almeno 3-4 service (EventCalendarService, OneToOneMatchingService, NotificationDispatcher, MediaUploadService).
- `app/Support/` (3 helper: ResolvesPublicMedia, VideoCompressor, VideoUploadLimits) — ok.
- `app/Policies/` — **non esiste**: grave per un progetto di questa dimensione.

Cartelle che dovrebbero esserci:
- `app/Actions/` o `app/UseCases/` per la logica di business (segui pattern di `lorisleiva/laravel-actions` o simile);
- `app/Listeners/` (vuota implicita): zero event-driven, le notifiche sono inviate inline nei controller.

### C.2 Routes (`routes/web.php`)

**Cose buone:**
- Throttling già attivo su POST sensibili (1:1, eventi, forum, messaggi, referrals).
- Localizzazione `/lingua/{locale}` accessibile anche guest.
- Separazione gruppi `auth+verified` vs `auth+verified+onboarding`.

**Problemi:**
- Le route `admin.cache.*` non hanno middleware ruolo (vedi #35).
- Le route notifiche sono nel gruppo `onboarding` (vedi #21).
- Manca route `terms`, `privacy`, `cookie-policy`.
- Manca route `health` esposta esplicitamente: c'è solo l'helper Laravel di default `/up`.

**Miglioramenti suggeriti:**
```php
Route::prefix('admin')->name('admin.')
    ->middleware(['auth', 'verified', 'role:super-admin|admin-community'])
    ->group(function () {
        Route::get('/cache', [CacheController::class, 'index'])->name('cache.index');
        Route::post('/cache/clear', [CacheController::class, 'clear'])->name('cache.clear');
    });
```

### C.3 Controller

| Controller | Linee | Stato | Note |
|------------|-------|-------|------|
| EventController | 245 | **Troppo lungo** | Estrarre EventCalendarService + EventInviter |
| OneToOneController | 366 | **Troppo lungo** | 175 righe in un solo metodo `index` |
| ProfileController | 278 | Pesante | Logica upload e normalizzazione URL duplicata |
| ConversationController | 146 | Ok | `conversationList()` da paginare |
| ForumController | 165 | Ok | |
| ReferralController | 149 | Ok | |
| SubscriptionController | 83 | Ok | |
| DashboardController | 49 | Ok | |
| DirectoryController | 90 | Ok | `inRandomOrder` da rivedere |
| OnboardingController | 61 | Ok | OK ma il wizard salva solo 8 campi |
| MemberOnepageController | 75 | Ok | |
| MediaController | 16 | Fragile | Vedi #10 |
| LocaleController | n/v | OK presumibilmente | |
| NotificationController | n/v | OK presumibilmente | |

**Pattern ricorrente da rifattorizzare:**
```php
// Codice attuale ripetuto 5+ volte
foreach (['website', 'linkedin_url', 'facebook_url'] as $field) {
    if (!empty($validated[$field])) {
        $url = trim($validated[$field]);
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }
        $validated[$field] = $url;
    }
}

// Soluzione: rule custom o cast
final class NormalizeUrl implements ValidationRule {
    public function validate(string $attribute, mixed $value, Closure $fail): void {
        // ...
    }
}
// poi sui form request
'website' => ['nullable', 'string', 'max:255', new NormalizeUrl()],
```

### C.4 Model

33 model. Relazioni corrette nel `User`. Punti deboli:

- **`MemberProfile::booted`** ha logica di business (lista d'attesa pianeti) dentro l'observer interno → andrebbe estratta in un Listener dedicato che ascolta `Saving` event.
- **`MemberProfile::$adminOverrideLimit`** flag statico globale: usare invece `MemberProfile::withoutLimitCheck(fn() => $profile->save())` con state scoped.
- **`Event::registrations`** vs **`Event::attendees`** ambiguità: due relazioni sulla stessa tabella, naming poco chiaro.
- **Manca `casts: array<string,string>` per molti enum**: ho visto MemberProfile castare `status` e `preferred_contact_method`, ma altri model (es. `Event::status`) lasciano string raw.
- **`User::activeSubscription()`** ritorna `?MemberSubscription` ma non è una relazione → non si può fare eager load → ogni dashboard fa N+1 sul subscription. Cambiare in `latestSubscription()` Eloquent relation.

### C.5 Migrations
- Migrations chiamate `2026_04_*` (timestamp futuro): è solo convenzione locale, **non è un bug** ma confonde. Ogni migration è ben strutturata, indici essenziali presenti (es. `index(['user_id', 'status'])` su subscriptions).
- C'è una `restructure_categories_professions_add_provinces` che riorganizza dati esistenti senza dichiarare `down()` reversibile: rischio in caso di rollback.
- Manca FK `chapter_id` su `events` con `cascadeOnDelete`? Da verificare.

### C.6 Eloquent — N+1
Punti dove ho visto rischio N+1 reale:
- `EventController::index` carica `$event->coverImageUrl()` in una map → metodo che usa `ResolvesPublicMedia`, ok perché non fa query.
- `DashboardController` carica `$user->load(...)` ma poi `optional($user->memberProfile)->category` non è in eager (`memberProfile.categories` mancante).
- `Forum show`: ottimo, fa già `with(['posts.user.memberProfile.city', 'replies.user.memberProfile.city'])`.
- `ConversationController::conversationList` fa `with(['participants.memberProfile', 'messages.user'])` ma poi `messages` viene caricata intera → vedi #6.

### C.7 Validazioni
Buone: rules dichiarative, enum + `Rule::in`, range numerici. Mancanze:
- Niente `current_password` su email change → la modifica email non richiede ri-autenticazione.
- Validazione URL fatta a mano via regex. Usare semplicemente Laravel rule `'url'` (ma attenzione: l'utente che inserisce "tuosito.it" senza https fallisce).
- File upload: `image` non è strict, va aggiunto `mimes:`.
- Telefono: solo `string|max:30` → accetta qualsiasi cosa. Aggiungere `regex:/^\+?[0-9 ]+$/` o usare `propaganistas/laravel-phone`.

### C.8 Gestione errori
- View `resources/views/errors/{403,404,419,429,500,503}.blade.php` esistono → ottimo.
- **Manca `withExceptions(...)` configurato** in `bootstrap/app.php`: vuoto. Andrebbe almeno:
  - `dontReport`/`report` per certe eccezioni;
  - reportable con Sentry (consigliato: `sentry/sentry-laravel`).

### C.9 Compatibilità produzione
- Hosting condiviso cPanel **niente SSH**: difficile fare `php artisan migrate` ad ogni deploy. Proposta: creare un endpoint `/admin/run-migrations?token=xxx` protetto, oppure usare il Job Scheduler di cPanel. Esiste già un `DEPLOY.md` — andrebbe esteso con istruzioni per migration via browser-token.
- `composer.json` richiede PHP `^8.2` → ok per cPanel, verificare versione MariaDB target (>= 10.4).
- Vendor 18 MB caricato come zip: pratica fragile, ad ogni update serve ricaricare zip e scompattare via File Manager.

---

## D. ANALISI FUNZIONALE DETTAGLIATA

### D.1 Registrazione e Login
- **Cosa funziona bene**: sistema referral integrato (link `?ref=codice` precompila `invitato da`), email lowercase forzata, password rules Laravel `Password::defaults()`.
- **Cosa manca**: CAPTCHA / honeypot anti-bot, social login (Google), conferma password sul cambio email, richiesta consenso privacy/T&C esplicito.
- **Cosa è poco chiaro**: il campo "Invitato da" è obbligatorio anche senza referral link → blocca registrazione organica.
- **Semplificazioni**: "Invitato da" facoltativo se referral_code è vuoto.

### D.2 Onboarding
- **Bene**: wizard a step, autosave step-by-step, design slick.
- **Manca**: foto profilo, professione, città, video. Tutti i campi obbligatori VERI sono lasciati al form profilo lungo (773 righe Blade).
- **Confusione**: l'utente completa il wizard, vede "🎉 profilo completato!", poi va in directory e si trova un avatar vuoto e profilo al 40%.
- **Soluzione**: dopo lo step 5 redirigere alla **vera** schermata profilo con barra "manca foto, professione, città" in alto.

### D.3 Directory
- **Bene**: filtri completi (ricerca, categoria, regione, provincia, città, chapter), paginazione, eager load.
- **Manca**: ordinamenti alternativi (più recenti, più attivi, alfabetico). Solo random.
- **Bug**: `inRandomOrder()` cambia ad ogni page-load — utente perde l'ordine se cambia pagina di paginazione.
- **Semplificazioni**: filtri come chip orizzontali, non sidebar piena; default "membri attivi nelle ultime 30 ore".

### D.4 One-to-One
- **Bene**: validazione avanzata (slot weekday + ora), conflitti detected, autoconfirm se rientra in slot, doppia conferma completamento.
- **Manca**: integrazione calendario (Google Calendar, .ics scaricabile), reminder via email/SMS, video meeting link auto-generato (Jitsi, Google Meet via API).
- **Confusione**: 366 righe controller, UI con 5 stati (pending/accepted/declined/rescheduled/cancelled/completed) → utente medio non capisce.
- **Semplificazioni**: ridurre a 3 stati visibili (Da confermare / Confermato / Concluso), gli altri come tag interni.

### D.5 Eventi
- **Bene**: 4 viste calendario, sistema inviti segmentato, quote (capacity), RSVP con 3 stati (interessato/partecipo/non partecipo), chapter-leader può creare eventi.
- **Manca**: pagamenti per eventi a pagamento, ticketing/QR-code check-in, integrazione Zoom/Meet, post-evento (foto, partecipanti).
- **Confusione**: troppi pannelli (calendario + dettaglio + form crea + form inviti). Non si capisce cosa sia "pannello centrale".
- **Semplificazioni**: lista prossimi default + dettaglio modale, calendario come secondary view.

### D.6 Forum
- **Bene**: categorie, thread, risposte annidate, proposte di nuove categorie.
- **Manca**: like/upvote, follow thread (notifica nuove risposte), markdown/rich editor, allegati, segnalazione moderazione, trending.
- **Confusione**: il forum è troppo grande per una community giovane. Senza 100 thread in due settimane sembra deserto.
- **Semplificazioni**: lanciare con 1-2 categorie pre-create + thread iniziali a cura admin. Renderlo "feed" più che "forum".

### D.7 Conversazioni
- **Bene**: 1:1 messaggi privati con notifica, ricerca, filtro non lette, last_read tracking.
- **Manca**: gruppi multipli, allegati, reactions, indicatore "sta scrivendo", presenza online (richiederebbe WebSocket).
- **Bug performance**: vedi #6.
- **Semplificazioni**: layout 2 colonne (lista + chat) invece di 3.

### D.8 Referral
- **Bene**: tracking pipeline (low/medium/high, valore stimato, stato), gating tramite 1:1 completato.
- **Manca**: dashboard ROI personale (quanti referral inviati, ricevuti, vinti, valore totale), notifica quando uno cambia stato, follow-up automatici.
- **Confusione**: il gating "solo dopo 1:1 completato" non è spiegato in UI → l'utente vede "nessun membro disponibile" senza capire perché.
- **Semplificazioni**: in UI mostrare "Hai bisogno di completare un 1:1 con questa persona prima di poter inviare un referral".

### D.9 Abbonamenti
- **Bene**: piani configurabili, trial, gating "directory_only" vs "directory_and_page", note pagamento, approvazione admin.
- **Manca**: gateway pagamento (Stripe/Mollie), fatturazione automatica (PDF + IVA), addebiti ricorrenti, dunning automation.
- **Confusione**: l'utente vede "Richiesta inviata, attendi approvazione" per giorni → tasso di conversione bassissimo.
- **Soluzione minima**: Stripe Checkout + webhook → approvazione automatica.

### D.10 Pannello Admin (Filament)
- **Bene**: 27 risorse, completezza, RelationManagers per join requests e member profiles.
- **Manca**: dashboard widget con metriche reali (nuovi membri/settimana, eventi creati, conversazioni attive, referral aperti, abbonamenti pendenti).
- **Confusione**: 27 voci di menu = scroll faticoso. Andrebbero raggruppate (es. "Configurazione" → categorie, professioni, tipologie; "Networking" → 1:1, referral; ecc.).

---

## E. ANALISI UX/UI DETTAGLIATA

### E.1 Coerenza visiva — il problema #1
Ho contato **6 sistemi di design diversi** nelle viste:

| Pagina | Background | Accent | Tipografia |
|--------|-----------|--------|-----------|
| Welcome (home) | dark navy + gradiente verde | `#6fa367` `#8fcf7d` | Plus Jakarta Sans |
| Login/Register | bianco con glow verde | `#10b981` emerald | sans default |
| Dashboard | dark navy + green | `#8BC53F` `#9AD84A` | sans default |
| Directory | bianco card | emerald + stone | Plus Jakarta Sans |
| Profile edit | bianco/stone | emerald + stone | Plus Jakarta Sans |
| Eventi | dark navy + cyan | `#8bc53f` `#49d1c4` | sans default |
| Forum | dark teal | `#79c843` `#2dd4bf` | sans default |
| Conversazioni | dark teal | `#79c843` `#55aa54` | sans default |
| One-to-one | dark navy + green | `#8BC53F` `#2DD4BF` | sans default |
| Referrals | (tema da verificare) | | |

**Conseguenze:**
- Il brand non esiste come sistema → nessuna riconoscibilità, no aura premium.
- L'utente non capisce dove si trova (la nav sopra è chiara, sotto è scuro: dissonanza).
- Cambiare un colore primario richiede oggi modifica a 18 file diversi.

**Soluzione (raccomandata):**
Definire **un solo design system in `resources/css/app.css`**:
```css
:root{
  /* Brand */
  --kommunity-primary:#55794F;       /* verde foresta */
  --kommunity-primary-2:#6FA367;     /* hover */
  --kommunity-accent:#2DD4BF;        /* teal */
  --kommunity-ink:#0B1E2A;           /* testo nero-blu */
  --kommunity-paper:#FAFAF7;         /* bianco caldo */
  --kommunity-line:#E8E5DD;
  --kommunity-warning:#D97706;
  --kommunity-danger:#DC2626;
  --kommunity-success:#059669;

  /* Tipografia */
  --kommunity-font-sans:'Plus Jakarta Sans', system-ui, sans-serif;
  --kommunity-font-serif:'Lora', serif;

  /* Spaziatura standard */
  --kommunity-radius-sm:.5rem;
  --kommunity-radius-md:1rem;
  --kommunity-radius-lg:1.5rem;
}
```

Poi **una sola scelta**: tutto light premium, OPPURE tutto dark premium. Non entrambi. Personalmente raccomando **light premium per area pubblica + onboarding** (più rassicurante, vende meglio) e **dark per area "operativa" giornaliera** (dashboard, conversazioni) se si vuole differenziare. Ma **deve essere una scelta consapevole**, non un caso.

### E.2 Tipografia
- Welcome usa `Plus Jakarta Sans 800` per H1 → ok.
- Dashboard usa `font-black` Tailwind (peso 900) ovunque → "shouting".
- Forms e dashboard mescolano `font-serif` (per H2) e sans → ok come pattern, ma non è applicato sempre.

**Raccomandazione**: Plus Jakarta Sans 600/700 per UI, Lora 600 per titoli editorial, max 3 size scale (16/18/22/28/40).

### E.3 Spaziature e gerarchia
Spaziature variabili: `p-7 lg:p-9` in dashboard, `p-6` altrove, `padding:1.45rem` inline. Niente sistema 4/8/12/16/24/32/48 px coerente.

### E.4 Pulsanti / CTA
- 4+ varianti di "primary": `km-button-primary`, `btn-primary`, `km-msg-primary`, `km-events-primary`. Stesse cose, naming diverso.
- Hover incoerente: alcuni hanno `transform: translateY(-2px)`, altri no.

**Soluzione**: 1 componente Blade `<x-button variant="primary|secondary|ghost|danger" size="sm|md|lg">`.

### E.5 Stati vuoti
Buoni nel forum e conversazioni (testo "Nessuna discussione"). Mancano:
- in directory: "Nessun membro corrisponde ai filtri";
- in 1:1: stato vuoto "Non hai ancora richieste — invita qualcuno";
- in eventi: "Nessun evento futuro nel tuo Pianeta";
- in referrals: "Inizia con un 1:1 per sbloccare i referral".

### E.6 Messaggi di errore e successo
Sistema di flash buono in `app.blade.php` con varianti warning/success/error/profile-updated. Però:
- L'errore "profile-updated" è hardcoded come messaggio, mentre dovrebbe essere `session('status')`.
- I messaggi sono molto tecnici: `'one-to-one-completion-confirmed'`, `'event-response-updated'` → questi sono key non testi.

### E.7 Navigation
- Il logo ha "Community professionale" sotto, in inglese sopra → mix linguistico.
- Su mobile la nav diventa hamburger ma le icone notifica sono solo desktop.

### E.8 Responsive
- Welcome mobile ok, hero collassa.
- Dashboard mobile: hero con `lg:grid-cols-[1.25fr_0.75fr]` collassa, ma il `K` decorativo invade.
- Conversazioni: layout 3 colonne fisso fa orizzontal-scroll su tablet portrait.
- Forum: stesso problema.
- Eventi: calendario gigantesco non leggibile su <768 px.

### E.9 Aspetto premium
Le single pagine prese una alla volta sono curate, ma l'**incoerenza** distrugge la percezione premium. L'utente che passa da `Login bianco minimal` → `Dashboard dark verde scuro` → `Profilo bianco minimal` → `Forum dark teal` percepisce un Frankenstein.

### E.10 Schermate principali — giudizio
| Schermata | Voto attuale | Problemi principali | Priorità |
|-----------|--------------|--------------------|---------:|
| Welcome | 7/10 | CSS inline 60KB, sezione mappa Italia decorativa pesante | Media |
| Login | 6.5/10 | Disallineato col resto del prodotto, label "Email" senza italiano forzato | Bassa |
| Register | 6/10 | Campo "Invitato da" obbligatorio, nessun consent GDPR | Alta |
| Onboarding wizard | 7.5/10 | Manca foto/professione/città | Alta |
| Dashboard | 5.5/10 | "Pianeta Roma" hardcoded, layout incoerente col resto | Alta |
| Profile edit | 5/10 | 773 righe blade, form-killer, manca tab/autosave | Alta |
| Directory | 7/10 | inRandomOrder lento, sidebar troppo larga | Media |
| Member onepage | 6.5/10 | Scroll lungo, mescola dati relazionali | Media |
| One-to-one | 6/10 | UI sovraccarica, 5 stati confusi | Alta |
| Eventi | 5/10 | 4 viste + 3 pannelli = troppo | Alta |
| Forum | 6/10 | Layout 3-col stretto su laptop | Media |
| Conversazioni | 5.5/10 | Performance + responsive | Alta |
| Referrals | 6/10 | Gating non spiegato in UI | Media |
| Subscriptions | 5/10 | Pagamento manuale = morto a lungo termine | Alta |
| Filament admin | 7/10 | 27 menu non raggruppati | Media |

---

## F. ANALISI TESTI E MICROCOPY

### F.1 Tono di voce
Mix di "tu" informale (corretto per community professionale), parole tecniche "Pianeta", italianismi forzati ("kommunity Kommunity" — bug nel testo `'Nuovo membro della kommunity Kommunity.'` in UserObserver). Glossario non definito.

### F.2 Glossario
Termini usati internamente:
- **Pianeta** = chapter (originale, ma non spiegato all'utente);
- **Onepage** = mini-sito personale;
- **One-to-one** = incontro privato tra due membri;
- **Referral** = opportunità business referenziata.

**Raccomandazione**: pagina `/glossario` o tooltip al primo utilizzo.

### F.3 Microcopy migliorabile

| Originale | Problema | Suggerito |
|-----------|----------|-----------|
| "Pianeta Roma · Lazio" (hardcoded ovunque) | Non veritiero | "{Nome del tuo Pianeta}" o "Nessun Pianeta assegnato" |
| "Nuovo membro della kommunity Kommunity." | Doppio "kommunity"+typo | "Benvenuto in Kommunity 👋" |
| "Profilo professionale in costruzione" (default onepage) | Negativo | "Sto preparando la mia presentazione" |
| "Profilo aggiornato con successo!" | Generico | "Modifiche salvate. La tua scheda è già aggiornata in directory." |
| "Completa il tuo profilo per accedere a questa sezione." | Tecnico | "Aggiungi qualche dettaglio in più al tuo profilo per usare questa sezione: bastano 2 minuti." |
| "Profilo inviato per la revisione" (post-onboarding) | Crea attesa indefinita | "Profilo inviato all'admin. Riceverai una mail entro 24 ore." (con SLA esplicito) |
| "event-response-updated" | È una chiave, non un testo | "La tua risposta all'evento è stata registrata." |
| "Hai già una richiesta in attesa di approvazione." | Stop secco | "Hai già una richiesta in attesa. Ti scriviamo appena l'admin la approva — di solito entro 24 ore." |
| "Nessun membro disponibile" (referral) | Non spiega perché | "Per inviare un referral a un membro devi prima completare un incontro 1:1 con lui. Vai su Directory e prenotane uno." |
| "Chi sei?" (onboarding step 1) | Filosofico | "Come ti chiami professionalmente?" |
| "La tua attività" (onboarding step 2) | Vago | "Cosa offri e cosa cerchi" |
| "Login" "Sign up" (bottoni) | Inglese | "Accedi" / "Registrati" — già fatto in IT, OK |

### F.4 Email/notifiche
Notifiche esistono solo come `DatabaseNotification`. Mancano email transazionali con copy curato per:
- Nuovo One-to-One ricevuto
- 1:1 confermato/declinato
- Reminder 1:1 24 ore prima
- Nuova referral ricevuta
- Nuovo messaggio (digest se >3 in 1 ora)
- Invito evento + reminder
- Risposta forum a tuo thread

**Template email da creare**: 8-10 email transazionali con design coerente, footer + privacy + unsubscribe.

### F.5 Pagine legali
**Mancano:** Termini, Privacy, Cookie Policy. **Bloccante** per un lancio EU.

---

## G. ANALISI SICUREZZA

### G.1 Configurazione
- `APP_DEBUG=false` ✅
- `APP_ENV=local` ❌ (deve essere `production` in prod)
- `APP_KEY` committata nel `.env` del workspace ❌ (rigenerare e segregare)
- `SESSION_ENCRYPT=false` ❌ (true in prod)
- `SESSION_SECURE_COOKIE` non impostato ❌ (true in HTTPS)
- `LOG_LEVEL=debug` ❌ (warning in prod)
- `MAIL_MAILER=log` ❌ (smtp in prod)

### G.2 Autenticazione
- Hash bcrypt con `BCRYPT_ROUNDS=12` ✅
- Verifica email obbligatoria via `MustVerifyEmail` ✅
- Throttle login Breeze default ✅
- 2FA: **assente** ❌ — c'è `pragmarx/google2fa-qrcode` in vendor ma non collegato. Aggiungere via Filament profile.
- Session lifetime 120 min ✅
- Password reset signed link ✅

### G.3 Autorizzazione
- **Zero Policy** → tutto via `abort_unless` (16 occorrenze). Vedi #2 e #35.
- Spatie Permission installato ma uso minimo (`role`, `can` qua e là).
- Pannello Filament: solo via `User::canAccessPanel`, basato su lista hardcoded ruoli.

### G.4 Protezione form
- CSRF: middleware Laravel attivo (default) ✅
- XSS: rischio su `{!! $body !!}` in register.blade (CMS Page → admin può iniettare JS). Vedi #39.
- SQL Injection: Eloquent ovunque, 1 solo `DB::raw` in `Chapter` controllato ✅
- Rate limiting: presente sui POST critici ✅, manca su register/login (Breeze ha throttle interno).
- File upload: `image|max:4096` su avatar, manca `mimes:` esplicito.

### G.5 Dati personali (GDPR)
- Nessun cookie banner ❌
- Nessun consenso esplicito a privacy policy in registrazione ❌
- Nessun export dati personali su richiesta ❌
- Nessun "elimina account" reale: c'è `ProfileController::destroy` ma cancella User con `cascadeOnDelete` su tutto → cosa rimane in db? Verificare. Aggiungere "soft delete con anonimizzazione".
- Email contatti memorizzati in `member_profiles.show_email` etc. ma niente check consent.
- IP dei post forum/messaggi: non loggati (bene per GDPR).

### G.6 Log e monitoring
- LOG_CHANNEL stack/single ok in dev, `daily` in prod consigliato.
- Niente Sentry / Bugsnag / monitoring.
- Niente alerting su errori 500.
- Activity log: nominato in README ma non vedo `spatie/laravel-activitylog` nel composer.

### G.7 Backup
- Niente backup db schedulato.
- Niente backup storage (gallery, video, avatar).
- **Bloccante per produzione**.

### G.8 Configurazione mail
- `MAIL_MAILER=log` in dev ok, ma serve account SMTP testato in prod.
- DKIM/SPF/DMARC sul dominio: da configurare lato cPanel.
- "noreply@kommunity.test" in default → cambiare con dominio reale.

### G.9 Cache
- `CACHE_STORE=database` → ok in cPanel, lento. Migliorabile a `file` in caso di hosting senza Redis.
- Nessun warmup `route:cache`, `view:cache`, `config:cache` schedulato post-deploy.

### G.10 Queue / Job
- `QUEUE_CONNECTION=database` ma **nessun worker configurato** → notifiche async non funzionano.
- Su cPanel senza SSH: usare Cron Job: `* * * * * php /home/.../artisan queue:work --stop-when-empty`.

### G.11 File esposti
- `vendor.zip` (18 MB) nella root: **rimuovere**.
- `.env.production.example` ok come template.
- 17 file `.bak` nel repo: **rimuovere**.

### G.12 Header HTTP
`public/.htaccess` configura X-Frame-Options, X-Content-Type-Options, HSTS, Referrer-Policy, Permissions-Policy → **molto buono**. Aggiungere CSP base.

---

## H. ANALISI PERFORMANCE

### H.1 Dashboard
5 query separate sempre eseguite, alcune con `with` profondo. Tempo stimato con 1k membri: 200-400 ms server-side. Migliorabile con cache 60s sulla parte "ultimi eventi" e "ultimi thread" che cambia raramente.

### H.2 Directory
`inRandomOrder` su `member_profiles` joinato a `users`, con 5 `whereHas` annidati. Su MySQL: `ORDER BY RAND()` → table scan. **Critico** sopra 5k membri.

Soluzione:
```php
// Cache 1 ora di un ordine "shuffle id"
$ids = Cache::remember("directory_shuffle_{date('YmdH')}", 3600,
    fn () => MemberProfile::where('is_active',true)->pluck('id')->shuffle()->all()
);
$members = MemberProfile::whereIn('id', $ids)
    ->orderByRaw("FIELD(id, ".implode(',', $ids).")")
    ->paginate(12);
```

### H.3 Eventi
- 4 query "future event" (registered/invited/next/default) eseguite sempre → 4 round-trip db.
- Carica `with(['attendees' => fn => select(...)])` per ogni evento del mese: con 30 eventi e 50 attendee a testa = 1.500 record.
- `Profession::all()`, `Region::all()`, `City::all()` se admin: 8.000 città italiane caricate sempre.

### H.4 Conversazioni
Vedi #6: bomba di performance.

### H.5 Forum
- `latestPost.user.memberProfile` correttamente eager loaded.
- `featuredThreads` query separata + `paginate(12)` su threads → 2 query separate stesso modello.
- `ForumPost::distinct('user_id')->count()` su tabella post: con 100k post = 1-2 sec.

### H.6 Asset / build
- Vite 7 con Tailwind 3 → build standard.
- `app.css` (verificato esistente) + 18 viste con `<style>` inline → CSS frammentato, nessun caching aggregato.
- Plus Jakarta Sans caricata da bunny.net → ok latenza ma esterno.
- Niente `asset()` versionato custom (Vite gestisce hash).
- `node_modules` presente nel repo: pesante per chi clona.

### H.7 Cache Laravel
- Niente warmup schedulato:
  - `route:cache` non utilizzato (le closure sui route impediscono cache);
  - `view:cache` ok in deploy;
  - `config:cache` ok in deploy.
- **Soluzione**: rimuovere closure in `web.php` (la home `Route::get('/', function …)` impedisce route cache). Spostare in un controller.

### H.8 Immagini
- Avatar/cover/intro_video: nessuna ottimizzazione (no thumbnail, no webp, no lazy-load coordinato).
- Suggerimento: `intervention/image` per generare 3 size (thumb/medium/full) all'upload, salvarle, servirle con `<picture>` srcset.

### H.9 Colli di bottiglia attesi
1. `inRandomOrder` directory.
2. `ConversationController` con 100+ thread.
3. Forum con 10k+ post.
4. Eventi calendar mese con 100+ eventi e 50+ attendee a testa.

---

## I. MIGLIORAMENTI IN ORDINE DI PRIORITÀ

### I.1 P0 — Bloccanti per qualunque lancio (1-3 giorni)
1. Sistemare `.env` reale di produzione (APP_ENV=production, URL https, DB MySQL, mail SMTP, session secure).
2. Eliminare 17 file `.bak` + `vendor.zip` dalla root.
3. Rimuovere "Pianeta Roma" hardcoded da dashboard, 1:1, copy onboarding.
4. Aggiungere middleware `role:super-admin|admin-community` su route `/admin/cache/*`.
5. Sanitizzare `{!! $body !!}` in `auth/register.blade.php` (HTMLPurifier).
6. Fixare `is_active => true` hardcoded in `ProfileController::update`.
7. Aggiungere pagine `/privacy`, `/termini`, `/cookie-policy` (copy + route + footer link) e cookie banner.
8. Configurare SMTP reale e abilitare email di notifica per: nuovo 1:1, nuovo messaggio, nuovo referral, conferma registrazione, conferma abbonamento.
9. Setup backup db schedulato (cron cPanel `mysqldump`).
10. Configurare queue worker su cron (`* * * * * php artisan queue:work --stop-when-empty`).

### I.2 P1 — Importanti per UX accettabile (4-7 giorni)
1. Unificare design system in `app.css`, eliminare CSS inline.
2. Creare 5 Policy: Event, OneToOne, Conversation, Referral, MemberOnepage.
3. Estrarre Service: EventCalendarService, OneToOneListing, NotificationDispatcher.
4. Spezzare `ProfileController::update` in 3 step con autosave.
5. Paginare `ConversationController::index`, caricare solo `lastMessage`.
6. Rimpiazzare `inRandomOrder` directory con shuffle cachato.
7. Onboarding wizard: aggiungere step "Foto + Professione + Città" obbligatori.
8. Notifiche email: 8 template HTML coerenti con brand.
9. Stati vuoti curati su tutte le pagine.
10. Componente `<x-button>` unico, eliminare 4 varianti.

### I.3 P2 — Per crescita (entro 30 giorni)
1. Stripe Checkout per abbonamenti, fatturazione automatica con IVA.
2. 2FA opzionale (google2fa-qrcode già in vendor).
3. Calendar export `.ics` per 1:1 ed eventi.
4. Reminder 24h prima 1:1/eventi.
5. Sentry / Bugsnag.
6. Filament dashboard widget: nuovi membri/settimana, eventi, abbonamenti pendenti, conversazioni attive.
7. Spatie Activity Log (per audit admin).
8. Search globale con cmd+K (Spotlight) usando Algolia/Meilisearch o LIKE base.
9. Editor markdown nel forum + tag.
10. Export GDPR su richiesta utente.

### I.4 P3 — Evoluzioni future
- App mobile (PWA prima, native poi).
- Video meeting embedded (Jitsi self-hosted).
- AI matching: "membri compatibili con i tuoi obiettivi di networking" via embedding bio.
- Riconoscimenti gamification (badge, livelli) per attivazione.
- Marketplace servizi tra membri (con commissione).
- API pubblica + Zapier/Make.

---

## J. ROADMAP CONSIGLIATA

### J.1 Interventi urgenti (oggi/domani — 1-2 giorni)
- [ ] Pulire `.bak`, `vendor.zip` dal repo
- [ ] Aggiornare `.env` produzione (verificare APP_ENV, APP_URL, DB, SMTP)
- [ ] Rigenerare APP_KEY in produzione
- [ ] Rimuovere "Pianeta Roma" hardcoded
- [ ] Middleware `role:` su admin route
- [ ] Sanitize HTML su Page CMS
- [ ] Cookie banner + pagine legali

### J.2 Entro 7 giorni
- [ ] 5 Policy create e collegate
- [ ] Email SMTP + notifiche email per 6 eventi chiave
- [ ] Backup automatico db + storage
- [ ] Queue worker funzionante via cron
- [ ] Onboarding wizard con foto/prof/città
- [ ] ConversationController paginato
- [ ] Directory `inRandomOrder` sostituito
- [ ] Stati vuoti su tutte le pagine
- [ ] 1 design system unificato (palette + tipografia)

### J.3 Entro 30 giorni
- [ ] CSS inline rimosso da tutte le viste
- [ ] Profile edit spezzato in 3 step
- [ ] EventController e OneToOneController refattorizzati in service
- [ ] Stripe Checkout integrato
- [ ] 2FA opzionale
- [ ] Test feature aggiunti per ogni controller (target 60% coverage)
- [ ] Sentry attivo
- [ ] Filament admin dashboard con widget metriche
- [ ] Email transazionali brand-consistent
- [ ] Documentazione tecnica DEPLOY.md aggiornata con procedura "no-SSH"

### J.4 Evoluzioni (60-180 giorni)
- [ ] PWA con offline shell
- [ ] AI matching membri
- [ ] Calendar `.ics` + integrazione Google Calendar
- [ ] Marketplace servizi
- [ ] Gamification badge

---

## K. CHECKLIST PRE-LANCIO

### K.1 Configurazione
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL=https://...` con HTTPS attivo
- [ ] `APP_KEY` rigenerata e fuori da git
- [ ] `SESSION_ENCRYPT=true`
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `LOG_LEVEL=warning` o `error`
- [ ] DB MySQL credenziali corrette
- [ ] SMTP testato (inviare email reale)
- [ ] FROM address dominio reale

### K.2 Sicurezza
- [ ] Eliminato `vendor.zip`
- [ ] Eliminati 17 `.bak`
- [ ] CSP attivo nel `.htaccess` (oltre HSTS già presente)
- [ ] 5 Policy implementate
- [ ] Route admin protette da role middleware
- [ ] Sanitize HTML su Page CMS
- [ ] Storage permessi corretti (775)
- [ ] `.env` non accessibile via web
- [ ] symlink `public/storage` funzionante

### K.3 Performance
- [ ] `php artisan config:cache`
- [ ] `php artisan view:cache`
- [ ] (route:cache se rimuovi closure su `/`)
- [ ] OPcache attivo lato PHP
- [ ] Indici DB verificati su colonne foreign + status + dates
- [ ] Compressione gzip attiva (già in `.htaccess` ✅)
- [ ] Cache asset 1 anno (già in `.htaccess` ✅)

### K.4 Funzionalità
- [ ] Test registrazione end-to-end (con email reale)
- [ ] Test verifica email reale
- [ ] Test creazione 1:1 + notifica destinatario
- [ ] Test creazione evento + invito + RSVP
- [ ] Test referral pipeline
- [ ] Test sottoscrizione abbonamento
- [ ] Test password reset
- [ ] Test cambio email
- [ ] Test eliminazione account

### K.5 Legale / Compliance
- [ ] Privacy Policy pubblicata e linkata in footer
- [ ] Termini & Condizioni
- [ ] Cookie Policy + banner
- [ ] Consenso esplicito GDPR su registrazione
- [ ] Email transazionali con link "annulla iscrizione"
- [ ] Indirizzo titolare trattamento dati visibile

### K.6 Backup / Monitoring
- [ ] Backup DB automatico (cron giornaliero)
- [ ] Backup storage settimanale
- [ ] Restore testato almeno una volta
- [ ] Sentry/Bugsnag attivo
- [ ] Uptime monitoring (UptimeRobot, Better Uptime)

### K.7 SEO / Marketing
- [ ] `<title>` dinamici per pagina
- [ ] `meta description` su ogni pagina pubblica
- [ ] OpenGraph + Twitter Card su welcome e onepage membri
- [ ] sitemap.xml
- [ ] robots.txt
- [ ] Google Analytics 4 + Cookie consent
- [ ] schema.org Organization + Person su onepage

---

## L. SUGGERIMENTI CONCRETI CON CODICE

### L.1 Esempio Policy
```php
// app/Policies/EventPolicy.php
namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function manage(User $user, Event $event): bool
    {
        if ($user->hasAnyRole(['super-admin', 'admin-community'])) return true;
        if ($user->can('gestire-eventi')) return true;
        return $event->chapter && $event->chapter->leader_id === $user->id;
    }

    public function register(User $user, Event $event): bool
    {
        return $event->is_published && $event->starts_at->isFuture();
    }
}

// In EventController:
$this->authorize('manage', $event);
```

### L.2 Esempio middleware role su admin
```php
Route::middleware(['auth', 'verified', 'role:super-admin|admin-community'])
    ->prefix('admin')->name('admin.')->group(function () {
        Route::get('/cache', [CacheController::class, 'index'])->name('cache.index');
        Route::post('/cache/clear', [CacheController::class, 'clear'])->name('cache.clear');
    });
```

### L.3 Esempio Conversation index ottimizzato
```php
// app/Models/Conversation.php
public function lastMessage(): \Illuminate\Database\Eloquent\Relations\HasOne
{
    return $this->hasOne(Message::class)->latestOfMany();
}

// Controller
$conversations = Conversation::query()
    ->whereHas('participants', fn ($q) => $q->where('users.id', $user->id))
    ->with(['participants:id,name', 'lastMessage'])
    ->latest('updated_at')
    ->paginate(20);
```

### L.4 Esempio fix Pianeta Roma hardcoded (dashboard)
```blade
{{-- prima --}}
<p class="km-eyebrow">Dashboard membro · Pianeta Roma</p>

{{-- dopo --}}
@php $chapter = $user->memberProfile?->chapter; @endphp
<p class="km-eyebrow">
    Dashboard membro
    @if($chapter)
        · {{ $chapter->name }}
    @endif
</p>
```

### L.5 Esempio sanitize HTML CMS
```php
use HTMLPurifier;
use HTMLPurifier_Config;

class SanitizesHtml
{
    public static function clean(?string $html): string {
        if (! $html) return '';
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,strong,em,ul,ol,li,a[href|target|rel],h2,h3,br');
        $config->set('HTML.TargetBlank', true);
        return (new HTMLPurifier($config))->purify($html);
    }
}

// In Filament / Page form: salvare via mutator del Model
public function setBodyAttribute($value): void {
    $this->attributes['body'] = SanitizesHtml::clean($value);
}
```

### L.6 Esempio onboarding step "obbligatorio foto + città"
```blade
{{-- _wizard.blade.php nuovo step prima dello step 1 --}}
<div x-show="step === 1" class="px-7 pb-8 pt-4">
    <p class="km-eyebrow">Step 1 di 5</p>
    <h2>Mostra chi sei</h2>
    <input type="file" x-on:change="uploadAvatar($event)" accept="image/*">
    <select x-model="fields.profession_id" required>
        <option value="">Seleziona la tua professione</option>
        @foreach($professions as $p)
            <option value="{{ $p->id }}">{{ $p->name }}</option>
        @endforeach
    </select>
    <select x-model="fields.city_id" required>
        <option value="">Seleziona la tua città</option>
        @foreach($cities as $c)
            <option value="{{ $c->id }}">{{ $c->name }}</option>
        @endforeach
    </select>
</div>
```

### L.7 Esempio queue worker via cron cPanel (no SSH)
```cron
# Cron cPanel — ogni minuto
* * * * * /usr/bin/php /home/USER/kommunity/artisan queue:work --stop-when-empty --max-time=55 --tries=3 >/dev/null 2>&1
# Backup db giornaliero alle 3am
0 3 * * * /usr/bin/mysqldump -uUSER -pPASS DBNAME | gzip > /home/USER/backups/db-$(date +\%Y\%m\%d).sql.gz
# Pulisci backup vecchi >30gg
0 4 * * * find /home/USER/backups -name "db-*.sql.gz" -mtime +30 -delete
```

### L.8 Esempio rate-limit register
```php
Route::post('register', [RegisteredUserController::class, 'store'])
    ->middleware('throttle:5,1');  // max 5 tentativi/min
```

### L.9 Microcopy onboarding migliorato
```text
Step 1 (era "Chi sei?"):
  Titolo: "Iniziamo dalle basi"
  Sottotitolo: "Servono solo 2 minuti. Potrai modificare tutto dal profilo."
  Field: "Nome dell'attività o studio (es. Studio Rossi & Associati)"

Step 2 (era "La tua attività"):
  Titolo: "Cosa offri, cosa cerchi"
  Sottotitolo: "Aiuta gli altri membri a capire come lavorare con te."

Step 5 (completamento):
  Titolo: "Ci siamo!"
  Sottotitolo: "Il profilo è in revisione. Ti scriviamo entro 24 ore quando sei visibile in directory. Nel frattempo:"
  Lista CTA chiare e cliccabili (oggi sono testo morto):
    [→ Carica una foto profilo]
    [→ Aggiungi un video di presentazione]
    [→ Imposta gli orari per i tuoi 1:1]
```

### L.10 Layout suggerito profile edit (3 tab + autosave)
```blade
<div x-data="{ tab: 'identita' }">
    <nav class="flex gap-2 border-b">
        <button @click="tab='identita'" :class="tab==='identita' ? 'tab-active' : ''">Identità</button>
        <button @click="tab='business'" :class="tab==='business' ? 'tab-active' : ''">Business</button>
        <button @click="tab='media'" :class="tab==='media' ? 'tab-active' : ''">Foto e video</button>
    </nav>

    <div x-show="tab==='identita'">{{-- nome, email, telefono, città --}}</div>
    <div x-show="tab==='business'">{{-- professione, categorie, bio, servizi, obiettivi --}}</div>
    <div x-show="tab==='media'">{{-- avatar, cover, video, gallery --}}</div>

    {{-- Salvataggio automatico ogni 30s o on-blur con piccolo toast --}}
</div>
```

---

## VOTI FINALI

| Dimensione | Voto |
|------------|-----:|
| **Tecnico** (architettura, codice, sicurezza) | **6 / 10** |
| **UX / UI** (coerenza, design, semplicità) | **5 / 10** |
| **Funzionale** (completezza feature) | **7.5 / 10** |
| **Prontezza al lancio** | **4 / 10** |

Motivazione sintetica:
- Tecnico 6/10: codice generalmente corretto, dominio ricco, ma layer sicurezza/policy vuoto, performance fragile, file `.bak` e `vendor.zip` lasciati nel repo.
- UX 5/10: 6 design system diversi, hardcoded "Pianeta Roma", form profilo da 773 righe.
- Funzionale 7.5/10: copertura ampia (1:1, eventi, forum, messaggi, referral, abbonamenti, CMS, multilingua) anche se metà delle feature ha gap di rifinitura.
- Prontezza al lancio 4/10: con `MAIL_MAILER=log`, `APP_ENV=local`, niente policy, niente backup, niente cookie banner, niente notifiche email reali, **non è consegnabile** a un cliente pagante oggi.

---

## TOP 10 — COSE DA SISTEMARE SUBITO (impatto ≫ effort)

1. **Pulire repo**: rimuovere 17 file `.bak`, `vendor.zip`, aggiungere a `.gitignore`.
2. **`.env` produzione corretto**: `APP_ENV=production`, `APP_URL` https reale, `MAIL_MAILER=smtp`, `LOG_LEVEL=warning`, `SESSION_ENCRYPT=true`.
3. **Eliminare "Pianeta Roma" hardcoded** ovunque (dashboard, 1:1 hero, copy onboarding).
4. **Middleware `role:`** sulle route `/admin/cache/*` (oggi qualunque utente loggato può clear cache).
5. **Notifiche email reali** per: registrazione completata, nuovo 1:1, nuovo messaggio, referral ricevuto, abbonamento approvato.
6. **Cookie banner + pagine legali** (privacy, termini, cookie policy) — bloccante GDPR.
7. **5 Policy**: Event, OneToOne, Conversation, Referral, MemberOnepage. Migrare i 16 `abort_unless` su `$this->authorize()`.
8. **Sanitize HTML** in `Page` CMS per evitare XSS stored.
9. **Sostituire `inRandomOrder`** in directory con shuffle cachato.
10. **Backup db automatico** + **queue worker** via cron cPanel.

---

## TOP 10 — FUNZIONI CHE RENDEREBBERO IL PRODOTTO PIÙ VENDIBILE

1. **Stripe Checkout** integrato per abbonamenti (con fatturazione PDF + IVA).
2. **AI Matching**: "membri compatibili con i tuoi obiettivi di networking" basato su embedding di bio + servizi (OpenAI o local). Differenziatore vero rispetto a BNI.
3. **Calendar sync** (Google Calendar / Outlook / .ics) per 1:1 ed eventi.
4. **Video meeting embedded** (Jitsi self-hosted o Daily.co): zero attrito per fare il 1:1.
5. **Reminder automatici** 24h e 1h prima del 1:1 / evento (email + push se PWA).
6. **App mobile / PWA** con notifiche push: copertura sempre presente.
7. **Dashboard analytics personale** per il membro: incontri fatti, valore referral generato, ROI sul prezzo abbonamento. Vendita interna potente.
8. **Gamification leggera** (badge "10 incontri", "5 referral chiusi", classifica annuale Pianeta) per aumentare attivazione.
9. **Marketplace servizi**: i membri possono pubblicare offerte/richieste con commissione opzionale per Kommunity.
10. **Onboarding "Concierge"**: dopo registrazione, un admin chiama l'utente o gli manda un Loom personalizzato in 24 ore. Conversion booster fortissimo.

---

## NOTE FINALI

- Il progetto **ha tutto il potenziale** per diventare una piattaforma SaaS professionale, ma oggi non è in condizione di raggiungere un primo cliente pagante.
- Il problema **non è "manca questa feature"** — è che le feature ci sono quasi tutte ma sono lasciate a metà rifinitura: design incoerente, copy hardcoded, sicurezza ad-hoc, niente automazioni.
- 2-3 settimane focalizzate sulla **roadmap P0 + P1** lo portano a un livello consegnabile.
- 60-90 giorni di lavoro mirato + integrazione Stripe + AI matching lo trasformano in un prodotto **vendibile** sul serio.

> Documento generato il 29 aprile 2026 da audit indipendente. File originale del progetto NON modificato (solo letture).
