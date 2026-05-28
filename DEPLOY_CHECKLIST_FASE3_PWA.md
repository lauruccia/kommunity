# DEPLOY CHECKLIST — Fase 3 PWA + Web Push
*(aggiornata 28 maggio 2026 — correzioni icone + traduzione EN)*

Pacchetto: **PWA installabile + Web Push notification con VAPID standard, bilingue IT/EN**.

> ⚠️ **Prerequisito**: Fase 0 deployata (`feature_flags` + concierge + reminder + analytics).

---

## A. Backup pre-deploy (5 min)

1. phpMyAdmin → Esporta DB Kommunity (Personalizzato → tutte le tabelle).
2. FileManager → zip della root del progetto.

---

## B. SQL da eseguire in phpMyAdmin

phpMyAdmin → DB → tab **SQL** → incolla il contenuto di:

```
database/sql/2026-05-01_pwa_push_schema.sql
```

Crea la tabella `push_subscriptions` con UNIQUE su `endpoint_hash` e FK su `users`.

---

## C. File da CARICARE / SOSTITUIRE via FileManager

### C.1 — File modificati (5)

| # | Path |
|---|------|
| 1 | `app/Providers/AppServiceProvider.php` |
| 2 | `config/services.php` |
| 3 | `routes/web.php` |
| 4 | `resources/views/layouts/app.blade.php` |
| 5 | `resources/views/partials/push-consent-banner.blade.php` |

### C.2 — File nuovi (18)

| # | Path |
|---|------|
| 1 | `database/sql/2026-05-01_pwa_push_schema.sql` |
| 2 | `database/migrations/2026_05_01_000001_create_push_subscriptions_table.php` |
| 3 | `app/Models/PushSubscription.php` |
| 4 | `app/Services/WebPush/VapidKeyGenerator.php` |
| 5 | `app/Services/WebPush/WebPushEncoder.php` |
| 6 | `app/Services/WebPush/WebPushService.php` |
| 7 | `app/Notifications/Channels/WebPushChannel.php` |
| 8 | `app/Console/Commands/GenerateVapidKeys.php` |
| 9 | `app/Http/Controllers/PushSubscriptionController.php` |
| 10 | `public/manifest.json` |
| 11 | `public/sw.js` |
| 12 | `public/js/km-push.js` |
| 13 | `public/images/icon-192.png` ← nome esatto (senza doppio .png) |
| 14 | `public/images/icon-512.png` ← nome esatto (senza doppio .png) |
| 15 | `public/images/badge-72.png` ← nome esatto (senza doppio .png) |
| 16 | `lang/it/push.php` |
| 17 | `lang/en/push.php` |
| 18 | `resources/views/partials/push-consent-banner.blade.php` |

> Crea le cartelle nuove se non esistono: `app/Services/WebPush/`, `app/Notifications/Channels/`, `public/js/`, `public/images/`.

### C.3 — Notification aggiornate (con `toWebPush` bilingue)

**Sovrascrivi** questi 6 file (gli altri 2 non avevano testo hardcoded):

| # | Path |
|---|------|
| 1 | `app/Notifications/OneToOneReceivedNotification.php` |
| 2 | `app/Notifications/OneToOneReminderNotification.php` |
| 3 | `app/Notifications/ReferralReceivedNotification.php` |
| 4 | `app/Notifications/SubscriptionApprovedNotification.php` |
| 5 | `app/Notifications/EventReminderNotification.php` |
| 6 | `app/Notifications/NewMemberConciergeAlertNotification.php` |

---

## D. CONFIGURAZIONE INIZIALE

### D.1 — Genera le chiavi VAPID (UNA SOLA VOLTA)

Da cPanel → **Cron Jobs** → **Add a Cron Job** → frequenza "Once per minute", comando:

```
cd /home/USER/public_html && /usr/local/bin/php artisan kommunity:generate-vapid-keys --save
```

Salva. Aspetta max 60 secondi. Poi:

1. FileManager → `storage/logs/` → apri `vapid-YYYYMMDD-HHMMSS.txt`
2. Copia le 3 righe `VAPID_PUBLIC_KEY=...`, `VAPID_PRIVATE_KEY=...`, `VAPID_SUBJECT=...`
3. Aggiungile in fondo a `.env`
4. **Cancella il file `vapid-*.txt`** (contiene la private key in chiaro)
5. **Disattiva il cron job** (cPanel → Cron Jobs → Delete)

> Alternativa: genera le chiavi in locale (`php artisan kommunity:generate-vapid-keys`) e copia l'output in `.env` remoto.

### D.2 — Pulisci cache

`https://kommunity.it/admin/cache` → **Pulisci tutte le cache**.

### D.3 — Icone PWA

⚠️ Le icone nel repo ora hanno il nome corretto (`icon-192.png`, non `icon-192.png.png`).
Carica in `public_html/images/` (non in `kommunity/public/images/`):

| File | Dimensione | Uso |
|------|------------|-----|
| `icon-192.png` | 192×192 | Home screen Android, Chrome install |
| `icon-512.png` | 512×512 | Splash screen |
| `badge-72.png` | 72×72 | Badge accanto alla notifica (Android) |

**Generarle velocemente**: https://realfavicongenerator.net/ o https://www.pwabuilder.com/imageGenerator

### D.4 — Attiva il feature flag

`/admin → Sistema → Feature Flags` → **"PWA + push notification"** → attiva ✅.

### D.5 — HTTPS obbligatorio

PWA + Push richiedono HTTPS. Verifica `APP_URL=https://kommunity.it` in `.env` e certificato attivo da cPanel → SSL/TLS.

---

## E. TEST POST-DEPLOY (15 min)

Tutti i test in **browser desktop** (Chrome o Firefox), in **incognito**:

1. ✅ Vai su `https://kommunity.it/dashboard` da loggata. Il banner notifiche deve apparire in alto a destra.
2. ✅ DevTools → Application → Manifest: carica senza errori. Service Workers: `sw.js` registrato e attivo.
3. ✅ Clicca "**Attiva**" / "**Enable**" (in base alla lingua). Il browser chiede permesso → accetta. Status: "✓ Notifiche attivate." / "✓ Notifications enabled."
4. ✅ DevTools → Application → Service Workers → bottone "**Push**" → inserisci `{"title":"test","body":"hello"}` → ricevi notifica.
5. ✅ Test backend come admin:

```javascript
fetch('/push/test', {
  method: 'POST',
  headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
    'Accept': 'application/json'
  }
}).then(r => r.json()).then(console.log)
```

Output atteso: `{ ok: true, delivered: 1 }`.

6. ✅ Ricevi una richiesta 1:1 da un account test → push automatica in lingua corretta.
7. ✅ Disattiva flag `pwa_push` → altra richiesta 1:1 → niente push (email arriva). Riattiva.
8. ✅ Su iPhone Safari: **Aggiungi a schermata Home** → apri dalla home → consenti notifiche. Ricevi push (iOS 16.4+).

---

## F. Troubleshooting

| Sintomo | Diagnosi | Fix |
|---------|----------|-----|
| Banner non appare | Feature flag OFF, o cookie `km_push_choice=never` | Verifica flag; in DevTools → Cookies elimina `km_push_choice` |
| Permesso negato | Utente ha bloccato a livello sito | DevTools → lucchetto → resetta permessi |
| `subscribe()` → `feature_disabled` | Flag OFF o VAPID assente in `.env` | D.4 + clear cache |
| `412 vapid_not_configured` | `.env` senza chiavi VAPID | D.1 + clear cache |
| Push `delivered=1` ma niente notifica | Tab in foreground | Apri un'altra tab e riprova |
| `openssl_pkey_derive()` undefined | PHP < 8.1 | Verifica versione PHP in cPanel |
| `pushManager.subscribe()` → AbortError | Browser non raggiunge push server | Verifica HTTPS + DNS |
| Icona rotta nella notifica | Hai caricato `icon-192.png.png` invece di `icon-192.png` | Ricarica con nome corretto |

---

## G. ROLLBACK

1. `/admin → Feature Flags` → disattiva `pwa_push`. Tutto torna come prima.
2. Rimozione completa: ripristina i 5 file modificati dal backup, elimina i 18 nuovi, droppa la tabella:
   ```sql
   DROP TABLE `push_subscriptions`;
   ```

---

## H. CHANGELOG (28 maggio 2026)

```
FIX  public/images/icon-192.png     — rinominato da icon-192.png.png
FIX  public/images/icon-512.png     — rinominato da icon-512.png.png
FIX  public/images/badge-72.png     — rinominato da badge-72.png.png
NEW  lang/it/push.php               — stringhe push in italiano
NEW  lang/en/push.php               — stringhe push in inglese
UPD  push-consent-banner.blade.php  — usa __('push.*') + STR JS object bilingue
UPD  OneToOneReceivedNotification   — toWebPush usa __('push.*')
UPD  OneToOneReminderNotification   — toWebPush usa __('push.*') + locale dinamico
UPD  ReferralReceivedNotification   — toWebPush usa __('push.*')
UPD  SubscriptionApprovedNotification — toWebPush usa __('push.*')
UPD  EventReminderNotification      — toWebPush usa __('push.*')
UPD  NewMemberConciergeAlertNotification — toWebPush usa __('push.*')
```
