# DEPLOY CHECKLIST — Fase 0 (30 aprile 2026)

Pacchetto modifiche per: **sistema feature flags + 3 feature rapide**.

> ⚠️ **Prerequisito**: deve essere già stato deployato il pacchetto della
> sessione precedente (`DEPLOY_CHECKLIST_2026-04-30.md`). Se non l'hai
> ancora fatto, **fallo prima** — questa Fase 0 dipende dal cookie banner
> e dal middleware `role:` registrati ieri.

---

## A. Backup pre-deploy (5 min)

1. **phpMyAdmin** → Esporta DB Kommunity (Personalizzato → tutte le tabelle → SQL).
2. **FileManager** → zip della root del progetto.

> Backup pre-edit dei file Cowork salvati lato locale in
> `outputs/backup_2026-04-30_fase0/` per rollback rapido.

---

## B. SQL da eseguire in phpMyAdmin (PRIMA dei file)

phpMyAdmin → DB Kommunity → **SQL** → incolla tutto il contenuto di:

```
database/sql/2026-04-30_fase0_schema.sql
```

Cosa fa:

- Crea tabella `feature_flags` con i 10 flag iniziali
- Aggiunge `reminder_24h_sent_at` + `reminder_1h_sent_at` su `one_to_one_requests`
- Aggiunge `concierge_assigned_at`, `concierge_assigned_to`, `concierge_completed_at`, `concierge_notes` su `users`
- Indice nuovo su `one_to_one_requests.requested_at` (perf reminder)
- Foreign key `users.concierge_assigned_to → users.id`

Risultato atteso: 3 nuove righe + 6 nuove colonne + 10 record in `feature_flags`
(3 attivi: `concierge_onboarding`, `reminders_one_to_one`, `analytics_personal` —
gli altri 7 disattivi in attesa delle prossime fasi).

---

## C. File da CARICARE / SOSTITUIRE via FileManager

### Modificati (5)

| # | Path |
|---|------|
| 1 | `app/Models/User.php` |
| 2 | `app/Observers/UserObserver.php` |
| 3 | `app/Http/Controllers/DashboardController.php` |
| 4 | `resources/views/dashboard.blade.php` |
| 5 | `routes/console.php` |

### Nuovi (12)

| # | Path |
|---|------|
| 1 | `database/migrations/2026_04_30_000001_create_feature_flags_table.php` |
| 2 | `database/migrations/2026_04_30_000002_add_reminders_to_one_to_one_requests.php` |
| 3 | `database/migrations/2026_04_30_000003_add_concierge_columns_to_users.php` |
| 4 | `database/sql/2026-04-30_fase0_schema.sql` |
| 5 | `app/Models/FeatureFlag.php` |
| 6 | `app/Services/Features.php` |
| 7 | `app/Services/MemberAnalyticsService.php` |
| 8 | `app/Filament/Resources/FeatureFlags/FeatureFlagResource.php` |
| 9 | `app/Filament/Resources/FeatureFlags/Pages/ListFeatureFlags.php` |
| 10 | `app/Filament/Widgets/PendingConciergeWidget.php` |
| 11 | `app/Notifications/NewMemberConciergeAlertNotification.php` |
| 12 | `app/Notifications/OneToOneReminderNotification.php` |
| 13 | `app/Console/Commands/SendOneToOneReminders.php` |
| 14 | `resources/views/partials/dashboard-analytics.blade.php` |

> **Crea le cartelle** se non esistono:
> `app/Services/`, `app/Filament/Resources/FeatureFlags/Pages/`,
> `app/Filament/Widgets/`, `database/sql/`.

---

## D. Cache Laravel (post-upload)

`https://kommunity.it/admin/cache` → **Pulisci tutte le cache**.

---

## E. Configurazione iniziale

### E.1 — Verifica i feature flag

`https://kommunity.it/admin → Sistema → Feature Flags`

Devi vedere 10 righe:
- ✅ Concierge Onboarding (engagement)
- ✅ Reminder 1:1 (24h e 1h) (reminders)
- ✅ Dashboard analytics membro (engagement)
- ❌ Stripe Checkout abbonamenti (payments)
- ❌ AI Matching membri (ai)
- ❌ Calendar sync (integrations)
- ❌ Video meeting embedded (Jitsi) (meetings)
- ❌ PWA + push notification (mobile)
- ❌ Gamification (engagement)
- ❌ Marketplace servizi (commerce)

I primi 3 sono **attivi**. Gli altri 7 sono placeholder per le fasi future.

### E.2 — Aggiungi il widget Filament alla dashboard admin

Per far apparire la lista "Concierge da contattare" nella dashboard
`/admin`, devi registrare il widget. Apri il file
`app/Providers/Filament/AdminPanelProvider.php` (se esiste — il path esatto
dipende dalla tua installazione Filament). Cerca il metodo `panel(Panel $panel)`
e aggiungi `PendingConciergeWidget::class` all'elenco `widgets()`.

> Se Filament v4 non ha ancora `AdminPanelProvider`, il widget si registra
> con auto-discovery: basta che il file esista in `app/Filament/Widgets/`.
> In quel caso ricarica `/admin` e dovresti vederlo.

---

## F. Cron cPanel

**Già configurato in Fase precedente** (sessione 30/04 mattina).
Il singolo cron `* * * * * php artisan schedule:run` copre anche i nuovi
schedule aggiunti in `routes/console.php`:
- `kommunity:send-event-reminders` (hourly) — esistente
- `kommunity:send-one-to-one-reminders` (hourly) — **nuovo**
- `app:db-backup` (dailyAt 03:15) — esistente
- `queue:work --stop-when-empty` (everyMinute) — esistente

Niente da aggiungere.

---

## G. TEST POST-DEPLOY (15 min)

1. ✅ **Login normale** → la dashboard mostra il widget "📈 Le tue performance"
   con i KPI calcolati (può essere a zero se sei un account fresh).
2. ✅ Vai su `/admin → Sistema → Feature Flags` → disattiva
   "Dashboard analytics membro" → ricarica la dashboard utente: il widget
   sparisce. Riattiva.
3. ✅ Crea un nuovo utente test (registrazione self-service o via
   Filament Users → Crea). Controlla che gli admin (super-admin/admin-community)
   ricevano l'email "🟢 Nuovo membro Kommunity — Concierge entro 24h".
4. ✅ Vai su `/admin` (dashboard Filament) → deve esserci il widget
   "Concierge onboarding · da contattare" con il nuovo utente in lista.
5. ✅ Clicca "Segna completato" → l'utente esce dalla lista.
6. ✅ Crea un 1:1 di test con `requested_at` impostato a domani alle stesse
   ore (24h) → forza l'esecuzione del comando da admin (può aspettare il cron):
   ```
   php artisan kommunity:send-one-to-one-reminders --dry-run
   ```
   Devi vedere il 1:1 nella lista. Senza `--dry-run` invia davvero.
7. ✅ Disattiva "Reminder 1:1" → riesegui il comando: deve rispondere
   "Feature flag disattivata. Skip."

---

## H. ROLLBACK

1. FileManager → ripristina i 5 file modificati dal backup
   `outputs/backup_2026-04-30_fase0/`.
2. Cancella i 12 file nuovi.
3. phpMyAdmin → SQL:
   ```sql
   DROP TABLE `feature_flags`;
   ALTER TABLE `one_to_one_requests`
       DROP INDEX `one_to_one_requested_at_idx`,
       DROP COLUMN `reminder_24h_sent_at`,
       DROP COLUMN `reminder_1h_sent_at`;
   ALTER TABLE `users`
       DROP INDEX `users_concierge_completed_idx`,
       DROP FOREIGN KEY `users_concierge_assigned_to_fkey`,
       DROP COLUMN `concierge_assigned_at`,
       DROP COLUMN `concierge_assigned_to`,
       DROP COLUMN `concierge_completed_at`,
       DROP COLUMN `concierge_notes`;
   ```

---

## I. ROADMAP FUTURO

Per le altre 7 feature, vedi `ROADMAP_2026_FEATURES.md` (creato in questa
sessione). Ordine consigliato:

1. **Fase 1** — Stripe (#1) → priorità alta perché monetizza subito
2. **Fase 2** — AI Matching (#2), Calendar sync (#3), Video Jitsi (#4) — differenziatore prodotto
3. **Fase 3** — PWA push (#6), Gamification (#8), Marketplace (#9) — engagement avanzato

Ogni feature è **opzionale** e attivabile con un singolo toggle quando
sarà pronta.

---

## J. CHANGELOG

```
+ feature_flags table + 10 flag preconfigurati
+ Features service centralizzato (con cache 30 min, auto-invalidation)
+ FeatureFlagResource Filament (Sistema → Feature Flags)
+ NewMemberConciergeAlertNotification (admin email su nuova reg)
+ PendingConciergeWidget (lista admin "da contattare")
+ OneToOneReminderNotification (24h e 1h)
+ SendOneToOneReminders artisan command (gated by flag)
+ MemberAnalyticsService (KPI personali con cache 10 min)
+ partials/dashboard-analytics.blade.php (widget responsive)
~ User model: fillable + casts per concierge_*
~ UserObserver: trigger concierge alert su nuova registrazione
~ DashboardController: passa $analytics gated
~ dashboard.blade.php: include partial analytics
~ routes/console.php: schedule send-one-to-one-reminders
```

Generato: 30 aprile 2026 — Fase 0 completa.
