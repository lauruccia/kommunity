# Kommunity — Istruzioni per Claude

## Stack tecnico

- **Backend**: Laravel 12, PHP 8.2+
- **Auth**: Laravel Breeze (sessioni, email verification)
- **Admin panel**: Filament 4 (`/admin`)
- **Permessi**: Spatie laravel-permission (roles/policies in `app/Policies/`)
- **Frontend**: Blade + Tailwind CSS 3 + Alpine.js 3
- **Build**: Vite 7 (`npm run build` → `public/build/`)
- **CSS custom**: `public/css/kommunity.css` — classi `.km-*`, nessun build necessario
- **Database**: MySQL (gestito via phpMyAdmin su cPanel)
- **i18n**: bilingue IT + EN — file in `lang/it/` e `lang/en/`
- **Helpers globali**: `app/helpers.php` (autoloaded via composer)

## Struttura cartelle chiave

```
app/
  Enums/              # enum PHP 8.1+
  Filament/           # Resources, Pages, Widgets per /admin
  Http/Controllers/   # controller web (Admin/ e Auth/ come sottocartelle)
  Models/             # Eloquent models
  Observers/          # model observers
  Policies/           # autorizzazioni Spatie
  Services/           # BannerService, ProfileAiRewriteService, WebPush, …
  View/               # View composers/components PHP

config/               # configurazioni Laravel
database/
  migrations/         # naming: YYYY_MM_DD_NNNNNN_*.php
  seeders/
lang/
  it/ en/             # auth.php, directory.php, nav.php, profile.php, push.php, subscription.php, validation.php
public/
  css/kommunity.css   # design system (classi .km-*)
  build/              # output Vite (gitignato, rigenerato in locale)
  images/ fonts/ js/  # asset statici versionati manualmente
resources/
  views/              # Blade: layouts/, components/, partials/, dashboard, forum, events, …
routes/
  web.php             # tutte le route (auth.php per Breeze)
```

## Workflow locale → produzione

### Setup locale (Laragon)
- PHP 8.2, MySQL, Apache via Laragon
- `composer install` e `npm install` solo in locale
- `npm run build` genera `public/build/` prima del push

### Git flow
```bash
git add .
git commit -m "feat: descrizione"
git push origin main
```

### Deploy su cPanel
1. cPanel → **Git Version Control** → **Update from Remote**
2. cPanel → **Git Version Control** → **Deploy HEAD**

Il file `.cpanel.yml` copia automaticamente:
- `public/css`, `public/js`, `public/images`, `public/fonts`, `public/build`, `public/brand`
- file singoli: `favicon.ico`, `manifest.json`, `sw.js`, `robots.txt`
- crea/permette `public_html/media/{avatars,logos,covers,gallery,videos}`
- svuota cache Blade compilate in `storage/framework/views/`

### Struttura server
```
/home2/kommunity/kommunity/    ← repo Laravel
/home2/kommunity/public_html/  ← web root (asset copiati da .cpanel.yml)
```

`public_html/index.php` punta a `../kommunity/vendor/` — **non toccare**.

### Migrazioni DB
- Eseguire in locale: `php artisan migrate`
- In produzione: SQL da eseguire manualmente via **phpMyAdmin**
- Ogni PR con nuove migration deve includere il corrispondente SQL grezzo

## Regole operative (OBBLIGATORIE)

1. **Non modificare `.env`** (né locale né produzione)
2. **Non usare comandi server** (`composer`, `npm`, `artisan`) in produzione — solo in locale
3. **Backup prima di ogni modifica**: creare copia `.bak` del file (es. `Controller.php.bak`)
4. **Bilingue obbligatorio**: ogni stringa visibile all'utente va in `lang/it/` E `lang/en/`
5. **CSS**: usare classi `.km-*` esistenti o aggiungerle a `public/css/kommunity.css`; Tailwind solo per layout/utility; nessun build per il CSS custom
6. **Body theme**: via `@push('body-class')` nel layout
7. **Asset statici** (immagini, font, js custom): vanno in `public/` e saranno copiati in `public_html/` dal deploy

## Output di ogni modifica

Ogni risposta che modifica codice deve includere:

1. **Elenco file modificati** (percorso relativo)
2. **Codice completo** di ogni file (non diff parziali)
3. **SQL** da eseguire in phpMyAdmin (se presente)
4. **Commit message** pronto da copiare
5. **Se serve aggiornare vendor**: avvisami esplicitamente

## Commit message pronti

Dopo ogni modifica fornisci sempre:
```bash
git add <file1> <file2> ...
git commit -m "tipo: descrizione breve"
git push origin main
```
Poi: "cPanel → Update from Remote → Deploy HEAD"

## Feature principali del progetto

- **Capitoli (Pianeti)**: community locali con ruoli e inviti (`ChapterMember`, `ChapterRole`, `PlanetRole`)
- **Directory membri**: profili, onepage pubblica, biglietto da visita digitale (`/card/{slug}`)
- **One-to-One**: richieste, note, followup tra membri
- **Forum**: categorie, thread, post (`ForumCategory`, `ForumThread`, `ForumPost`)
- **Eventi**: registrazioni, inviti, ruoli target
- **Messaggi**: conversazioni private (`Conversation`, `Message`)
- **Referral**: sistema inviti con codice
- **Abbonamenti**: piani e sottoscrizioni (`SubscriptionPlan`, `MemberSubscription`)
- **Banner advertising**: campagne, creatività, placement, impression, click
- **Push notifications**: `PushSubscription`, service worker `public/sw.js`
- **PWA**: `manifest.json`, service worker
- **Admin Filament**: gestione completa in `/admin` (Resources per ogni entità)
- **Feature flags**: `FeatureFlag` model + `Features` service
- **AI rewrite profilo**: `ProfileAiRewriteService`
