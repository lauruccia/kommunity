# Deploy su cPanel — Checklist Kommunity

## Struttura cartelle su cPanel
Il document root del dominio deve puntare a `/kommunity/public/`  
(non alla root del progetto, ma alla sottocartella `public/`).

---

## Primo deploy (o dopo aggiornamenti importanti)

### 1. Carica i file
Carica tutti i file del progetto tramite FTP/File Manager **escludendo**:
- `node_modules/`
- `.env` (crealo direttamente sul server)
- `storage/logs/*.log` (se non vuoi portare i log locali)

### 2. Configura il .env sul server
Crea il file `.env` nella root del progetto copiando `.env.production.example` e compilando tutti i valori:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tuodominio.it
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=nome_database
DB_USERNAME=utente_db
DB_PASSWORD=password
SESSION_SECURE_COOKIE=true
LOG_LEVEL=warning
MAIL_MAILER=smtp
# ... ecc.
```

### 3. Esegui da terminale cPanel (SSH o Cron Job "una volta")

```bash
# Installa dipendenze senza pacchetti dev
composer install --no-dev --optimize-autoloader

# Esegui le migrazioni
php artisan migrate --force

# Crea il link simbolico storage → public/storage
php artisan storage:link

# Ottimizza per la produzione (cache config, route, view)
php artisan optimize

# Svuota cache vecchia se aggiorni file di config
php artisan optimize:clear && php artisan optimize
```

### 4. Permessi cartelle (su cPanel via SSH o File Manager)
```bash
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

---

## Aggiornamenti successivi (deploy incrementale)

```bash
# 1. Attiva la modalità manutenzione
php artisan down --render="errors.503"

# 2. Carica i nuovi file via FTP

# 3. Aggiorna dipendenze se hai modificato composer.json
composer install --no-dev --optimize-autoloader

# 4. Esegui eventuali nuove migrazioni
php artisan migrate --force

# 5. Svuota e rigenera cache
php artisan optimize:clear
php artisan optimize

# 6. Riattiva il sito
php artisan up
```

---

## Verifica post-deploy

- [ ] `APP_DEBUG=false` nel .env
- [ ] `APP_ENV=production` nel .env
- [ ] `SESSION_SECURE_COOKIE=true` (sito su HTTPS)
- [ ] `LOG_LEVEL=warning` (non debug)
- [ ] `MAIL_MAILER=smtp` (non log)
- [ ] `php artisan optimize` eseguito
- [ ] `php artisan storage:link` eseguito
- [ ] Cartella `public/` è il document root del dominio
- [ ] File `.env` non accessibile via browser (testa: `https://tuodominio.it/.env`)
- [ ] Pagine errore personalizzate funzionanti (testa: `https://tuodominio.it/pagina-che-non-esiste`)
- [ ] Login e registrazione funzionanti
- [ ] Upload file funzionante (test avatar)

---

## Variabili .env modificate in questo rilascio
| Variabile | Valore sviluppo | Valore produzione |
|-----------|----------------|-------------------|
| APP_ENV | local | **production** |
| APP_DEBUG | true | **false** |
| LOG_LEVEL | debug | **warning** |
| LOG_CHANNEL | stack | **daily** |
| SESSION_SECURE_COOKIE | (non impostato) | **true** |
| SESSION_ENCRYPT | false | **true** |
| MAIL_MAILER | log | **smtp** |
| DB_CONNECTION | sqlite | **mysql** |

---

## Comandi ottimizzazione disponibili

```bash
php artisan optimize          # Compila config + route + view cache
php artisan optimize:clear    # Svuota tutta la cache
php artisan route:cache       # Solo cache route
php artisan config:cache      # Solo cache config
php artisan view:cache        # Solo cache view
php artisan event:cache       # Cache eventi
```

> **Nota:** Dopo ogni modifica a file .env, config/, o routes/ devi rieseguire `php artisan optimize`.
