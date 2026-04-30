# DEPLOY CHECKLIST — Fase 3 PWA + Web Push (1 maggio 2026)

Pacchetto: **PWA installabile + Web Push notification con VAPID standard**.

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

### Modificati (5)

| # | Path |
|---|------|
| 1 | `app/Providers/AppServiceProvider.php` |
| 2 | `config/services.php` |
| 3 | `routes/web.php` |
| 4 | `resources/views/layouts/app.blade.php` |
| 5 | Le 7 Notification esistenti (vedi sezione D) |

### Nuovi (16)

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
| 13 | `public/images/icon-192.png` ⚠️ (vedi punto E.3) |
| 14 | `public/images/icon-512.png` ⚠️ (vedi punto E.3) |
| 15 | `public/images/badge-72.png` ⚠️ (vedi punto E.3) |
| 16 | `resources/views/partials/push-consent-banner.blade.php` |

> Crea le cartelle nuove: `app/Services/WebPush/`, `app/Notifications/Channels/`, `public/js/`, `public/images/`.

### D. Notification aggiornate (con `toWebPush`)

7 file modificati — è stato aggiunto il channel `web_push` al via() e un metodo `toWebPush()`. **Sovrascrivi** sul server:

| # | Path |
|---|------|
| 1 | `app/Notifications/OneToOneReceivedNotification.php` |
| 2 | `app/Notifications/OneToOneStatusChangedNotification.php` |
| 3 | `app/Notifications/OneToOneReminderNotification.php` |
| 4 | `app/Notifications/NewMessageNotification.php` |
| 5 | `app/Notifications/ReferralReceivedNotification.php` |
| 6 | `app/Notifications/SubscriptionApprovedNotification.php` |
| 7 | `app/Notifications/EventReminderNotification.php` |
| 8 | `app/Notifications/NewMemberConciergeAlertNotification.php` |

---

## E. CONFIGURAZIONE INIZIALE

### E.1 — Genera le chiavi VAPID (UNA SOLA VOLTA)

Da cPanel → **Cron Jobs** → **Add a Cron Job** → frequenza "Once per minute" (o just imposta una data nel passato), comando:

```
cd /home/USER/public_html && /usr/local/bin/php artisan kommunity:generate-vapid-keys --save
```

Salva. Aspetta che giri (massimo 60 secondi). Poi:

1. Apri FileManager → `storage/logs/`
2. Trova il file `vapid-YYYYMMDD-HHMMSS.txt`
3. Aprilo → copia le 3 righe `VAPID_PUBLIC_KEY=...`, `VAPID_PRIVATE_KEY=...`, `VAPID_SUBJECT=...`
4. Aggiungile in fondo a `.env`
5. **Cancella il file `vapid-*.txt`** (contiene la private key in chiaro)
6. **Disattiva il cron job** (cPanel → Cron Jobs → Delete)

> Alternativa offline: scarica `artisan` + `app/` + `vendor/` in locale, esegui `php artisan kommunity:generate-vapid-keys` sul tuo computer (richiede PHP 8.2 in locale), copia output in `.env` remoto.

### E.2 — Pulisci cache

`https://kommunity.it/admin/cache` → **Pulisci tutte le cache**.

### E.3 — Icone PWA (CRITICO)

Il `manifest.json` punta a 3 icone che **devi creare** e caricare in `public/images/`:

| File | Dimensione | Uso |
|------|------------|-----|
| `icon-192.png` | 192×192 | Home screen Android, Chrome install |
| `icon-512.png` | 512×512 | Splash screen, store listings |
| `badge-72.png` | 72×72 | Badge piccolo accanto alla notifica (Android) |

**Come generarle velocemente** (3 opzioni):

1. **Generatore online**: https://realfavicongenerator.net/ → carichi un PNG/SVG quadrato del logo Kommunity, scarichi tutto il pacchetto, prendi i 3 file giusti.
2. **Da logo SVG esistente**: se hai un logo SVG, https://www.pwabuilder.com/imageGenerator lo converte in tutte le size.
3. **Placeholder temporaneo**: prendi un PNG quadrato qualsiasi del logo Kommunity, ridimensionalo con qualunque tool (Photoshop, GIMP, Preview Mac), salva 3 versioni.

Senza queste icone l'install PWA fallisce ma il push **funziona comunque** (l'icona di default del browser viene mostrata).

### E.4 — Attiva il feature flag

`/admin → Sistema → Feature Flags` → trova **"PWA + push notification"** → attiva il toggle ✅.

### E.5 — HTTPS obbligatorio

PWA + Push richiedono HTTPS per funzionare (eccetto `localhost`). Verifica che il tuo dominio kosmopay/kommunity sia in HTTPS — `APP_URL=https://...` in `.env` e certificato attivo da cPanel → SSL/TLS.

---

## F. TEST POST-DEPLOY (15 min)

Tutti i test in **browser desktop** (Chrome o Firefox), in **incognito**:

1. ✅ Vai su `https://kommunity.it/dashboard` loggata. Il banner "🔔 Notifiche immediate" deve apparire in alto a destra.
2. ✅ DevTools → Application → Manifest: deve caricarsi senza errori. Service Workers: `sw.js` registrato e attivo.
3. ✅ Clicca "**Attiva**" sul banner. Il browser chiede permesso → accetta. Status testo: "✓ Notifiche attivate."
4. ✅ DevTools → Application → Service Workers → bottone "**Push**" (lato dx). Inserisci `{"title":"test","body":"hello"}` → ricevi una notifica.
5. ✅ Test backend: visita `https://kommunity.it/push/test` (POST) come admin. Ricevi push reale tramite il push server della tua connection.
6. ✅ Ricevi una richiesta 1:1 da un altro account test → push automatica con titolo "🤝 Nuova richiesta 1:1".
7. ✅ Disattiva il feature flag `pwa_push` → manda altra richiesta 1:1 → niente push (ma email arriva ancora). Riattiva.
8. ✅ Vai su iPhone con Safari, apri il sito, **Aggiungi a schermata Home**. Apri l'app dalla home → consenti notifiche dal banner. Ricevi push (iOS 16.4+).

### Comando test rapido push admin

Da console browser come admin:

```javascript
fetch('/push/test', {
  method: 'POST',
  headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
    'Accept': 'application/json'
  }
}).then(r => r.json()).then(console.log)
```

Output atteso: `{ ok: true, delivered: 1 }` e push notification visibile.

---

## G. Troubleshooting

| Sintomo | Diagnosi | Fix |
|---------|----------|-----|
| Banner non appare | Feature flag OFF, o cookie `km_push_choice=never` settato | Verifica flag, in DevTools → Application → Cookies elimina `km_push_choice` |
| Permesso negato dal browser | Utente ha bloccato a sito-livello | DevTools → padlock → resetta permessi |
| `subscribe()` fallisce con `feature_disabled` | Flag OFF o VAPID assente in `.env` | Vai a E.4, poi `/admin/cache` clear |
| Push test risponde `412 vapid_not_configured` | `.env` non ha `VAPID_PUBLIC_KEY`/`VAPID_PRIVATE_KEY` | E.1 + clear cache |
| Push delivered=1 ma niente notifica visibile | Tab focused → push mostra solo se tab non in foreground | Apri altra tab e riprova |
| `openssl_pkey_derive()` undefined | PHP < 8.1 | Verifica versione PHP cPanel — minimo 8.1 |
| `pushManager.subscribe()` errore "AbortError" | Browser non riesce a contattare push server | Verifica HTTPS + DNS — di solito firewall locale |

---

## H. ROLLBACK

1. `/admin → Feature Flags` → disattiva `pwa_push`. Tutto torna come prima senza altre modifiche.
2. Per rimuovere completamente il codice: ripristina i 5 file modificati dal backup, elimina i 16 nuovi, droppa la tabella:
   ```sql
   DROP TABLE `push_subscriptions`;
   ```

---

## I. CHANGELOG

```
+ Tabella push_subscriptions con FK + UNIQUE endpoint_hash
+ PushSubscription model con scope active() + revoke()
+ VapidKeyGenerator (EC P-256, openssl_pkey_new)
+ WebPushEncoder (RFC 8291 aes128gcm: ECDH + HKDF + AES-128-GCM)
+ WebPushService (VAPID JWT ES256 + HTTP POST + 410 handling)
+ WebPushChannel (Laravel notification channel custom)
+ Comando artisan kommunity:generate-vapid-keys
+ PushSubscriptionController (subscribe/unsubscribe/test/vapidKey)
+ public/manifest.json (PWA installabile con shortcuts)
+ public/sw.js (push event + notificationclick)
+ public/js/km-push.js (subscription manager client)
+ partials/push-consent-banner.blade.php (banner discreto)
~ AppServiceProvider: registra channel 'web_push'
~ config/services.php: webpush.{public_key,private_key,subject}
~ routes/web.php: 4 route /push/*
~ layouts/app.blade.php: <link manifest> + theme-color + banner
~ 8 Notification esistenti: aggiunto channel web_push + toWebPush()
```

Generato: 1 maggio 2026 — Fase 3 PWA + Web Push.
