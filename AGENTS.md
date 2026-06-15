# Agents — Istruzioni per sessioni AI su Kommunity

Questo file definisce come gli agenti AI devono comportarsi su questo progetto.

## Regole obbligatorie

1. **Leggi sempre `CLAUDE.md`** prima di iniziare qualsiasi modifica.
2. **Aggiorna `AI_CONTEXT.md`** dopo ogni sessione significativa (data, file toccati, decisioni).
3. **Aggiorna `CHANGELOG_AI.md`** dopo ogni modifica al codice.
4. **Crea copia `.bak`** di ogni file prima di modificarlo.
5. **Bilingue obbligatorio**: qualsiasi stringa visibile va in `lang/it/` E `lang/en/`.

## Prima di modificare

- Verifica le dipendenze del file (chi lo usa, chi lo chiama).
- Non rinominare rotte o metodi pubblici senza cercare tutti i riferimenti.
- Non modificare migrazioni già eseguite — crea sempre una nuova migration.

## CSS e frontend

- Usa classi `.km-*` esistenti in `public/css/kommunity.css`.
- Aggiungi nuove classi `.km-*` solo in quel file, senza build.
- Tailwind solo per layout/utility generici.
- Body theme via `@push('body-class')`.

## Database

- Migrazioni: eseguire in locale con `php artisan migrate`.
- In produzione: fornire sempre il SQL grezzo da eseguire via phpMyAdmin.

## Deploy

Dopo ogni modifica:
```bash
git add <files>
git commit -m "tipo: descrizione"
git push origin main
```
Poi su cPanel: **Update from Remote** → **Deploy HEAD**.

## File da non toccare mai

- `.env` (locale e produzione)
- `public_html/index.php` (server)
- `vendor/` (rigenerato da composer)
- `public/build/` (rigenerato da Vite)

## Riferimenti rapidi

- Contesto progetto: `AI_CONTEXT.md`
- Mappa struttura: `PROJECT_MAP.md`
- Log modifiche: `CHANGELOG_AI.md`
- Istruzioni complete: `CLAUDE.md`
