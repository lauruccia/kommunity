# Kommunity

Piattaforma community professionale per networker, imprenditori e professionisti costruita su Laravel 12.

## Stato attuale

Questo repository contiene il primo backbone operativo del progetto:

- Laravel 12 scaffoldato nella cartella `C:\laragon\www\kommunity`
- autenticazione Blade con Breeze e verifica email
- Filament 4 installato con pannello admin su `/admin`
- Spatie Permission installato e collegato al modello `User`
- schema dati iniziale per profili membri, onepage, directory, capitoli, agenda one-to-one, eventi, forum, messaggi, referenze e activity log
- observer che crea automaticamente `member_profiles` e `member_onepages` quando nasce un utente
- homepage brandizzata `Kommunity`
- directory interna su `/directory` con ricerca testuale, filtri e ordine casuale
- onepage membro su `/member/{slug}`
- seed demo con ruoli, admin, capitolo, membri, evento e referral

## Stack

- PHP 8.2.12 locale
- Laravel 12.56
- Blade
- Tailwind CSS
- Alpine.js
- Filament 4
- Spatie Laravel Permission
- SQLite di default per il bootstrap locale

## Vincoli ambiente rilevati

- Il brief richiede PHP `8.3+`, ma l'ambiente attuale usa PHP `8.2.12`.
- Il build frontend funziona, ma `Node 21.7.1` non e' nel range consigliato da Vite 7. L'ambiente corretto e' `20.19+` oppure `22.12+`.

## Setup locale

1. Duplica `.env.example` in `.env` se necessario.
2. Aggiorna database e URL locale.
3. Esegui:

```powershell
composer install
npm install
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
```

4. Avvia l'app:

```powershell
composer run dev
```

## Credenziali demo

- Admin Filament: `admin@kommunity.test` / `password`
- Membri demo:
  - `giulia@kommunity.test`
  - `marco@kommunity.test`
  - `sara@kommunity.test`
  - `elena@kommunity.test`

Password per tutti: `password`

## Architettura iniziale

### Dominio

- `member_profiles`: profilo business del membro
- `member_onepages`: mini sito professionale automatico
- `chapters`: capitoli territoriali o tematici
- `availability_slots` e `one_to_one_requests`: agenda relazionale
- `events` e `event_registrations`: eventi community
- `forum_*`: feed e discussioni
- `conversations` e `messages`: messaggistica privata
- `referrals`: opportunita' e referenze business

### Flussi gia' impostati

- registrazione utente
- verifica email
- provisioning automatico profilo + onepage
- accesso dashboard membro
- accesso directory interna autenticata
- visualizzazione onepage membro
- accesso pannello admin solo per ruoli `super-admin` e `admin-community`

## Prossimi incrementi consigliati

- completare onboarding guidato con tutti i campi del brief
- creare Resource Filament per utenti, profili, capitoli, eventi e referenze
- implementare policy e permessi granulari
- sviluppare CRUD dashboard per modifica onepage e disponibilita' one-to-one
- aggiungere workflow reali per messaggi, forum, RSVP eventi e notifiche
- migrare da SQLite a MySQL/MariaDB per l'ambiente target
