# Contesto progetto

Questo progetto è già stato analizzato.

## Stack
- Framework: Laravel 12
- Linguaggio: PHP 8.2+
- Database: MySQL (gestito via phpMyAdmin su cPanel)
- Frontend: Blade + Tailwind CSS 3 + Alpine.js 3
- Backend: Laravel (MVC), Filament 4 per /admin, Laravel Breeze per auth
- Auth: Laravel Breeze (sessioni, email verification)
- Permessi: Spatie laravel-permission (roles/policies)
- Build: Vite 7 (`npm run build` → `public/build/`)

## Struttura principale
- Rotte: `routes/web.php` (+ `routes/auth.php` per Breeze)
- Controller: `app/Http/Controllers/` (sottocartelle `Admin/`, `Auth/`)
- Viste: `resources/views/` (layouts/, components/, partials/, dashboard, forum, events, members, one-to-ones, conversations, notifications, onboarding, subscriptions, referrals, card, invito, directory, …)
- Componenti principali: Filament Resources in `app/Filament/Resources/`, View composers in `app/View/`
- Middleware: Laravel standard + Spatie permission + `auth` + `verified`
- Modelli principali (47 totali): `User`, `Chapter`, `ChapterMember`, `ChapterRole`, `ChapterInvitation`, `ChapterJoinRequest`, `PlanetRole`, `MemberProfile`, `MemberOnepage`, `MemberGalleryImage`, `ForumCategory`, `ForumThread`, `ForumPost`, `ForumCategoryProposal`, `Conversation`, `Message`, `OneToOneRequest`, `OneToOneNote`, `OneToOneFollowup`, `OneToOneReference`, `Event`, `EventRegistration`, `EventInvitation`, `SubscriptionPlan`, `MemberSubscription`, `BannerCampaign`, `BannerCreative`, `BannerPlacement`, `BannerClick`, `BannerImpression`, `Advertiser`, `PushSubscription`, `FeatureFlag`, `Referral`, `Page`, `SiteSetting`, `City`, `Province`, `Region`, `Sector`, `Category`, `Profession`, `AvailabilitySlot`, `ProfileSuggestion`, `ProfileVideoAccessRequest`, `CompanyInterestType`

## Vincoli funzionali
- Profilo: max **3 professioni** selezionabili (`profession_ids` max:3 in `ProfileUpdateRequest` + limite UI in `kmMultiSelect` con parametro `maxSelected`). I padri gerarchici auto-inclusi in `ProfileController@update` non contano nel limite.

## Flussi da non rompere
- Login: Laravel Breeze, sessioni, email verification (`routes/auth.php`)
- Registrazione: Breeze + referral tracking (`Referral` model)
- Pagamenti: `SubscriptionPlan`, `MemberSubscription` — gestione abbonamenti
- Dashboard: `DashboardController` → `resources/views/dashboard.blade.php`
- Invio notifiche: `PushSubscription`, service worker `public/sw.js`, `app/Services/WebPush/`
- Chat pianeta: `PlanetChatController` + polling JSON + push via `WebPushService`
- API: nessuna API pubblica — tutto via web routes

## Enums (`app/Enums/`)
`ContactMethod`, `EventAttendanceStatus`, `EventType`, `MemberProfileStatus`, `OneToOneStatus`, `OnepageVisibility`, `PaymentMethod`, `ReferralStatus`, `SubscriptionPlanType`, `SubscriptionStatus`

## Gestione 419/CSRF (non rompere)
- Il render callback in `bootstrap/app.php` è registrato su `HttpException` con check status 419, NON su `TokenMismatchException`: in Laravel 12 `prepareException()` la converte prima dei callback.
- `layouts/guest.blade.php` ricarica la pagina se ripristinata da bfcache (`pageshow` + `e.persisted`) per evitare token CSRF stantii su mobile.

## Policies (`app/Policies/`)
`ConversationPolicy`, `EventPolicy`, `MemberOnepagePolicy`, `OneToOnePolicy`, `ReferralPolicy`

## Services (`app/Services/`)
`BannerService`, `Features` (feature flags), `MemberAnalyticsService`, `ProfileAiRewriteService`, `ProfileCompletionService`, `WebPush/`

## Observers (`app/Observers/`)
`UserObserver`

## Filament Resources (`app/Filament/Resources/`) — 30 risorse
Advertisers, AvailabilitySlots, BannerCampaigns, BannerCreatives, BannerPlacements, Categories, Chapters, Cities, CompanyInterestTypes, Conversations, Events, FeatureFlags, ForumCategories, ForumCategoryProposals, ForumThreads, MemberOnepages, MemberProfiles, MemberSubscriptions, OneToOneRequests, Pages, Permissions, PlanetRoles, Professions, ProfileSuggestions, Referrals, Regions, Roles, Sectors, SubscriptionPlans, Users

## Migrazioni
43 migrazioni in `database/migrations/`

## i18n
- `lang/it/` e `lang/en/`: `auth.php`, `directory.php`, `nav.php`, `planet_chat.php`, `profile.php`, `push.php`, `subscription.php`, `validation.php`

## Convenzioni
- Non modificare `.env` (né locale né produzione).
- Non eseguire `composer`, `npm`, `artisan` in produzione — solo in locale.
- Non cambiare nomi di rotte esistenti.
- Non rimuovere funzioni senza verificare dipendenze.
- Ogni stringa visibile all'utente va in `lang/it/` E `lang/en/`.
- CSS: usare classi `.km-*` in `public/css/kommunity.css`; Tailwind solo per layout/utility.
- Body theme via `@push('body-class')` nel layout.
- Creare copia `.bak` prima di modificare qualsiasi file.
- Quando modifichi qualcosa, aggiorna questo file.

## Ultime modifiche
Data: 2026-06-19 — One-to-one: la conferma di completamento (anche di una sola parte) blocca riprogrammazione/annullamento (`OneToOneRequest::completionStarted()`); nuova colonna `one_to_one_requests.rescheduled_by` + `canRespondTo()` per far confermare la riprogrammazione alla controparte (passa subito ad Accettato). Fix CSS hover `.km-button-secondary` su tema scuro in `kommunity.css`. Semplificata `auth/verify-email`. Dettagli in `CHANGELOG_AI.md`.

## Ultima analisi
Data: 2026-06-10
Cosa è stato analizzato: struttura completa del progetto (46 modelli, controller, 30 risorse Filament, enums, policies, services, observers, rotte, viste, 42 migrazioni)
Decisioni prese: file di contesto AI aggiornati con dati reali scansionati dal repository
File importanti:
- `CLAUDE.md` — istruzioni operative complete
- `routes/web.php` — tutte le rotte
- `public/css/kommunity.css` — design system (.km-*)
- `app/helpers.php` — helper globali (autoloaded)
- `.cpanel.yml` — deploy automation
- `public/sw.js` — service worker PWA/push
- `public/manifest.json` — PWA manifest
