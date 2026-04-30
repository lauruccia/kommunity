# ROADMAP 2026 — Le 10 nuove feature di Kommunity

Ultimo aggiornamento: 30/04/2026
Stato attuale: **Fase 0 implementata** (feature flag system + 3 feature rapide).

Tutte le feature sono **opzionali**: ognuna è gated da un record nella tabella
`feature_flags` e si attiva/disattiva da `/admin → Sistema → Feature Flags`
senza redeploy.

---

## ✅ FASE 0 — Già fatta in questa sessione

| # | Feature | Flag | Stato |
|---|---------|------|-------|
| 5 | Reminder 1:1 24h e 1h | `reminders_one_to_one` | ✅ Codice + cron |
| 7 | Dashboard analytics personale | `analytics_personal` | ✅ Codice + view |
| 10 | Concierge onboarding | `concierge_onboarding` | ✅ Codice + widget Filament |
| — | **Sistema feature flags** | (infra) | ✅ Tabella + Service + Filament |

**Effort totale Fase 0**: ~12 ore lavoro.

---

## 🔵 FASE 1 — Monetizzazione (priorità ALTA)

### #1 — Stripe Checkout abbonamenti — flag `stripe_checkout`

**Scelte già fatte**: PDF semplice + IVA 22% (no SDI in MVP).

**Effort stimato**: 3-5 giorni full-time.

**Dipendenze esterne**:
- Account Stripe (test + live keys)
- Libreria `stripe/stripe-php` (caricata manualmente in `vendor/` da
  https://github.com/stripe/stripe-php/releases — scegli ultimo zip stabile,
  estrai in `vendor/stripe/stripe-php/`, aggiorna `vendor/composer/autoload_psr4.php`
  con `'Stripe\\' => array($vendorDir . '/stripe/stripe-php/lib')`).
- `dompdf/dompdf` per le ricevute PDF (idem caricamento manuale).

**Tabelle nuove**:
```sql
CREATE TABLE `payment_intents` (
  id, user_id, subscription_plan_id,
  stripe_session_id, stripe_payment_intent_id,
  amount, currency, vat_amount, total_amount,
  status (pending|succeeded|failed|refunded),
  invoice_pdf_path, created_at, updated_at, paid_at
);
```
+ aggiungere `stripe_customer_id` su `users` e `stripe_price_id` su `subscription_plans`.

**File da creare**:
- `app/Services/StripeCheckoutService.php` — Stripe Checkout session
- `app/Http/Controllers/StripeWebhookController.php` — webhook `checkout.session.completed`
- `app/Services/InvoicePdfService.php` — DOMPDF + template Blade `invoices/invoice.blade.php`
- `app/Filament/Resources/PaymentIntents/PaymentIntentResource.php` — admin view
- Route POST `/abbonamento/checkout/{plan}` (gated)
- Route POST `/stripe/webhook` (csrf bypass — `Route::post('stripe/webhook')` fuori dal middleware web)
- View `subscriptions/checkout-success.blade.php`

**.env aggiunte**:
```
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
INVOICE_VAT_RATE=22.00
INVOICE_COMPANY_NAME="..."
INVOICE_COMPANY_VAT="..."
INVOICE_COMPANY_ADDRESS="..."
```

**Settings su feature flag** (JSON in `feature_flags.settings`):
```json
{ "vat_rate": 22, "currency": "EUR", "live_mode": false }
```

**Test plan**:
1. Crea piano Stripe in modalità test
2. Submit pagamento test card `4242 4242 4242 4242`
3. Verifica webhook arriva → subscription approved → email
4. Verifica PDF generato in `storage/app/invoices/`
5. Switch in `live_mode` solo dopo che la fatturazione SDI/commercialista è ok

**Watch-out**:
- IVA 22%: assicurati di considerare reverse charge per UE (Stripe Tax automatizza, decisione MVP è "no Tax — IVA fissa Italia 22%")
- Se vendi a P.IVA UE serve VIES validation → fase 2

---

### #10 — (già fatto in Fase 0)

---

## 🟢 FASE 2 — Differenziatore prodotto (priorità MEDIA)

### #2 — AI Matching — flag `ai_matching`

**Scelte già fatte**: OpenAI text-embedding-3-small.

**Effort stimato**: 2-4 giorni.

**Dipendenze esterne**: Account OpenAI + `OPENAI_API_KEY`. Niente SDK — useremo `Http::post` diretto sull'endpoint REST (zero composer).

**Tabelle nuove**:
```sql
CREATE TABLE `member_embeddings` (
  id, user_id, source_text MEDIUMTEXT,
  embedding_vector LONGTEXT,  -- JSON array 1536 float
  embedding_hash VARCHAR(64), -- SHA256 del source_text per evitare ricomputi
  generated_at TIMESTAMP,
  UNIQUE(user_id)
);

CREATE TABLE `member_match_scores` (
  user_a_id, user_b_id, score DECIMAL(6,5),
  computed_at TIMESTAMP,
  UNIQUE(user_a_id, user_b_id),
  INDEX idx_user_a_score (user_a_id, score DESC)
);
```

**File da creare**:
- `app/Services/EmbeddingsService.php` — wrapper `Http::post('https://api.openai.com/v1/embeddings')`
- `app/Console/Commands/RefreshMemberEmbeddings.php` — schedule daily, ricomputa solo se profilo cambiato (hash check)
- `app/Console/Commands/RefreshMatchScores.php` — schedule daily, calcola cosine similarity tra ogni coppia (cap a top-50 per utente per non esplodere)
- `app/Http/Controllers/MatchingController.php` — endpoint `/directory/match` che restituisce top-N membri
- View `directory/matches.blade.php`
- Widget dashboard "Membri compatibili"

**.env aggiunte**:
```
OPENAI_API_KEY=sk-...
OPENAI_EMBEDDING_MODEL=text-embedding-3-small
```

**Settings flag**:
```json
{ "model": "text-embedding-3-small", "top_k_per_user": 20, "min_score": 0.55 }
```

**Note di scaling**:
- Cosine similarity in PHP: O(N²) — ok fino a ~5k membri (~25M operazioni)
- Sopra 5k membri serve Pinecone/Qdrant → fase 4

---

### #3 — Calendar sync — flag `calendar_sync`

**Effort stimato**: 4-7 giorni (OAuth complesso).

**Dipendenze**:
- Google Cloud Console: registra app OAuth, redirect URL `https://kommunity.it/oauth/google/callback`
- Microsoft Azure: registra app OAuth, redirect URL `https://kommunity.it/oauth/microsoft/callback`
- Niente composer: useremo `Http::post` diretto su token endpoint Google/MS

**Tabelle nuove**:
```sql
CREATE TABLE `calendar_connections` (
  id, user_id, provider ENUM('google','microsoft'),
  access_token TEXT (encrypted), refresh_token TEXT (encrypted),
  expires_at, scope, calendar_id, created_at, updated_at,
  UNIQUE(user_id, provider)
);
```

**File da creare**:
- `app/Services/Calendar/GoogleCalendarService.php`
- `app/Services/Calendar/MicrosoftCalendarService.php`
- `app/Services/Calendar/IcsExporter.php` — genera `.ics` per qualsiasi evento/1:1
- `app/Http/Controllers/OAuthController.php` (callback redirect)
- Route `/profile/calendar` UI per connettere/disconnettere
- Listener `OneToOneRequestUpdated` → sync evento sui calendari connessi

**Endpoint .ics download (zero auth)**:
- `GET /events/{event}/calendar.ics` — pubblico ma legato all'evento
- `GET /one-to-one/{request}/calendar.ics` — richiede auth + partecipante

---

### #4 — Video meeting embedded — flag `video_embedded`

**Scelta già fatta**: Jitsi public meet.jit.si (gratis, zero auth).

**Effort stimato**: 1-2 giorni.

**Tabelle**: nessuna nuova. Aggiungere colonna `video_room_token` su `one_to_one_requests`.

**File da creare/modificare**:
- `app/Services/JitsiService.php` — genera URL stanza random `meet.jit.si/kommunity-{token}` con token 16 char
- View `one-to-ones/_video.blade.php` — iframe Jitsi quando il 1:1 è in corso (-15min / +1h dall'orario)
- Pulsante "Avvia meeting" nelle email reminder
- Listener su `one_to_one_requests` accepted → genera token

**Settings flag**:
```json
{ "domain": "meet.jit.si", "early_join_minutes": 15, "tail_minutes": 60 }
```

---

### #7 — (già fatto in Fase 0)

---

## 🟡 FASE 3 — Engagement avanzato (priorità BASSA-MEDIA)

### #6 — PWA + push notification — flag `pwa_push`

**Effort stimato**: 3-5 giorni.

**Dipendenze**:
- Generazione VAPID keys (1 volta sola)
- iOS push richiede HTTPS + manifest.json valido + utente che installa l'app

**File da creare**:
- `public/manifest.json`
- `public/sw.js` (service worker)
- `app/Services/WebPushService.php` (POST diretto a FCM endpoint, niente composer)
- Tabella `push_subscriptions(user_id, endpoint, p256dh_key, auth_key)`
- Channel notification `WebPushChannel`

**.env**:
```
VAPID_PUBLIC_KEY=...
VAPID_PRIVATE_KEY=...
VAPID_SUBJECT=mailto:info@kommunity.it
```

---

### #8 — Gamification — flag `gamification`

**Effort stimato**: 2-3 giorni.

**Tabelle**:
```sql
CREATE TABLE `badges` (
  id, slug UNIQUE, name, description, icon, condition_type, condition_value, points
);
CREATE TABLE `user_badges` (
  user_id, badge_id, earned_at, UNIQUE(user_id, badge_id)
);
```

**File da creare**:
- Seed badge: "Primo 1:1", "10 incontri", "5 referral chiusi", "1° anno", "Top contributor capitolo"
- `app/Services/BadgeAwardService.php` con check periodico
- Listener `OneToOneCompleted` / `ReferralWon` → `BadgeAwardService::checkForUser($user)`
- Widget dashboard "I tuoi badge"
- Pagina `/classifica` per chapter
- Schedulato weekly per badge cumulativi

---

### #9 — Marketplace servizi — flag `marketplace`

**Effort stimato**: 5-8 giorni (è quasi un sotto-prodotto).

**Tabelle nuove** (5):
```sql
CREATE TABLE `marketplace_listings` (...);
CREATE TABLE `marketplace_categories` (...);
CREATE TABLE `marketplace_inquiries` (...);
CREATE TABLE `marketplace_transactions` (...);
CREATE TABLE `marketplace_commissions` (...);
```

**File da creare** (~15):
- 3 controller (Listings, Inquiries, Transactions)
- 3 policy
- 8 view
- Filament resource per moderazione
- Integrazione opzionale Stripe Connect per commissione

**Decisione critica fase 2**: marketplace = funziona dentro Kommunity o
porta gli utenti fuori? Stripe Connect o link esterno?

---

## STIMA TEMPI COMPLESSIVI

| Fase | Feature | Giorni | Settimane |
|------|---------|--------|-----------|
| Fase 0 | (3 feature + infra) | **fatto** | — |
| Fase 1 | Stripe | 3-5 | 1 |
| Fase 2 | AI + Calendar + Video | 7-13 | 2-3 |
| Fase 3 | PWA + Gamif + Marketplace | 10-16 | 3-4 |

**Totale**: 6-9 settimane di sviluppo full-time per arrivare a tutte le 10 feature.

---

## REGOLE TRASVERSALI (valide per ogni fase)

1. **Sempre opzionale**: ogni feature ha il suo `feature_flag`. Disattivata =
   il codice torna invisibile, niente errori, niente performance hit.
2. **Fail-soft**: se un servizio esterno (Stripe/OpenAI/Google) è giù, il
   resto della piattaforma deve continuare a funzionare. Tutti i wrapper
   gestiscono `\Throwable` e fanno log + skip.
3. **Cache aggressiva**: ogni read da DB o API esterna dietro
   `Cache::remember(...)` 5-30 min, invalidato dagli observer.
4. **Compatibilità shared hosting**: nessun composer richiesto a runtime.
   Le dipendenze esterne (stripe-php, dompdf, ecc.) si caricano via
   FileManager dentro `vendor/` esistente.
5. **No SSH**: ogni modifica DB è SQL eseguibile in phpMyAdmin **e**
   migration file (per chi userà artisan in futuro).
6. **Ogni rilascio**: backup pre-deploy + checklist test post-deploy.

---

## NOTE PER LE PROSSIME SESSIONI

- Quando inizieremo Fase 1, comunicami: account Stripe, P.IVA, indirizzo
  legale, email per le ricevute (per popolare `INVOICE_*` in `.env`).
- Per Fase 2 (AI), comunica `OPENAI_API_KEY` quando vuoi attivare.
- Per Fase 2 (Calendar), serve registrare app su Google Cloud Console e
  Microsoft Azure; ti darò io i passaggi esatti quando inizieremo.
