# Referenze 2.0 — Valore generato e premi

Proposta concreta e implementazione per la revisione del sistema **Referenze**.

## 1. Il problema da risolvere

Il flusso reale è a **tre attori**:

1. **Segnalatore** (Francesco) — fa parte della Kommunity, conosce un bisogno.
2. **Professionista** (il commercialista) — anche lui in Kommunity, riceve il lavoro.
3. **Cliente segnalato** (l'amico Fabbro) — la persona che ha bisogno del servizio.

> Francesco al commercialista: «Ti segnalo il mio amico Fabbro che ha bisogno di un commercialista.»
> A consulenza conclusa il commercialista dichiara: «Grazie a Francesco ho fatto una consulenza da 1.000 €.»

Il valore così generato deve essere **tracciato, validato e premiato**: chi porta più valore alla Kommunity sale in classifica e potrà ricevere premi.

Nel sistema attuale mancavano tre cose: la **dichiarazione del valore realizzato** da parte del professionista, una **validazione** di quel valore, e una **classifica** che trasformi il valore in punti.

## 2. Decisioni di prodotto adottate

| Tema | Scelta |
|------|--------|
| Chi riceve i punti/premi | **Il segnalatore** (chi genera valore per la Kommunity) |
| Validazione del valore | **Approvazione admin** prima che entri in classifica |
| Cliente segnalato | Resta come testo libero (campi *Azienda / cliente* e *Contatto*) |

## 3. Nuovo ciclo di vita degli stati

Stati ridisegnati (più chiari, sostituiscono i vecchi `in_charge/contacted/negotiating/won/lost/archived`):

| Stato | Significato | Chi lo imposta |
|-------|-------------|----------------|
| **Inviata** (`sent`) | Segnalazione appena creata | Segnalatore (automatico) |
| **In corso** (`in_progress`) | Il professionista l'ha presa in carico | Professionista |
| **Conclusa (da validare)** (`completed`) | Valore dichiarato, in attesa di controllo | Professionista (dichiara importo) |
| **Confermata** (`confirmed`) | Valore validato → **conta per classifica e premi** | Admin |
| **Annullata** (`cancelled`) | Non andata a buon fine | Professionista/Admin |
| **Valore rifiutato** (`rejected`) | Importo non approvato | Admin |

I dati storici vengono migrati automaticamente: `in_charge/contacted/negotiating → in_progress`, `won → confirmed` (con valore = stima), `lost/archived → cancelled`.

## 4. Il flusso completo

1. **Segnalo** → creo la referenza verso il professionista (stato *Inviata*).
2. **Prende in carico** → il professionista clicca "Prendi in carico" (*In corso*).
3. **Dichiara il valore** → a lavoro concluso il professionista clicca **💶 Dichiara valore consulenza**, inserisce l'importo (es. 1.000 €) ed eventuale esito (*Conclusa, da validare*). Segnalatore e admin vengono notificati.
4. **Validazione admin** → un admin **Approva** (l'importo entra in classifica, stato *Confermata*) oppure **Rifiuta** (*Valore rifiutato*). Può correggere l'importo dal pannello.
5. **Classifica** → il valore confermato aggiorna automaticamente i punti del segnalatore.

## 5. Dati: nuove colonne su `referrals`

| Colonna | Tipo | Uso |
|---------|------|-----|
| `declared_value` | DECIMAL(12,2) | Importo dichiarato dal professionista |
| `declared_at` | TIMESTAMP | Quando è stato dichiarato |
| `approved_value` | DECIMAL(12,2) | Importo validato dall'admin (conta per i premi) |
| `approved_at` | TIMESTAMP | Quando è stato validato |
| `approved_by` | FK users | Admin che ha validato |

(`estimated_value`, `is_public`, `acknowledged_at` già presenti.)

## 6. Sistema di punteggio e premi

I punti vanno **al segnalatore**, solo per referenze in stato *Confermata*:

```
punti = (n. consulenze confermate × 50) + (valore confermato totale ÷ 10)
```

- **50 punti** per ogni consulenza andata a buon fine → premia il *volume*.
- **1 punto ogni 10 €** di valore validato → premia chi genera *alto valore*.

Esempio: 3 consulenze confermate per 1.000 € + 5.000 € + 2.000 € = `3×50 + 8000/10` = **950 punti**.

La formula è centralizzata in `ReferralScoreService` (costanti `PUNTI_BASE` ed `EURO_PER_PUNTO`): si possono ritarare i pesi senza toccare il resto. Su questa base si possono poi definire i **premi** (es. soglie a punti, badge, classifica mensile/annuale azzerando il periodo via `approved_at`).

## 7. Dove si vede

- **Membri** (`/referenze`): pulsante "Dichiara valore", pill con valore dichiarato/confermato sulle righe, nuovo tab **🏆 Classifica** con i propri punti e la top dei generatori di valore.
- **Admin** — tab *Moderazione*: pulsanti **Approva/Rifiuta** sulle referenze "da validare".
- **Pannello Filament** (`/admin`): colonne valore, filtro per stato, azioni Approva/Rifiuta, badge di navigazione col numero di referenze in attesa, e **widget Classifica** in dashboard.

## 8. File toccati

**Backend:** `app/Enums/ReferralStatus.php`, `app/Models/Referral.php`, `app/Http/Controllers/ReferralController.php`, `app/Policies/ReferralPolicy.php`, `routes/web.php`, `app/Services/ReferralScoreService.php` (nuovo), `app/Notifications/ReferralValueDeclaredNotification.php` + `ReferralConfirmedNotification.php` (nuovi), `database/migrations/2026_06_19_120000_add_referral_value_validation_columns.php` (nuovo).

**Admin/UI:** `app/Filament/Resources/Referrals/ReferralResource.php`, `app/Filament/Widgets/ReferralLeaderboardWidget.php` (nuovo), `resources/views/referrals/index.blade.php`.

**Lingua:** `lang/it/referrals.php` + `lang/en/referrals.php` (nuovi), `lang/{it,en}/push.php`.

## 9. SQL per phpMyAdmin (produzione)

```sql
ALTER TABLE `referrals`
  ADD COLUMN `declared_value` DECIMAL(12,2) NULL AFTER `estimated_value`,
  ADD COLUMN `declared_at`    TIMESTAMP    NULL AFTER `declared_value`,
  ADD COLUMN `approved_value` DECIMAL(12,2) NULL AFTER `declared_at`,
  ADD COLUMN `approved_at`    TIMESTAMP    NULL AFTER `approved_value`,
  ADD COLUMN `approved_by`    BIGINT UNSIGNED NULL AFTER `approved_at`;

ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_approved_by_foreign`
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- Normalizzazione stati storici → nuovo ciclo di vita
UPDATE `referrals` SET `status`='in_progress' WHERE `status` IN ('in_charge','contacted','negotiating');
UPDATE `referrals` SET `approved_value`=`estimated_value`, `approved_at`=`updated_at`, `status`='confirmed' WHERE `status`='won';
UPDATE `referrals` SET `status`='cancelled' WHERE `status` IN ('lost','archived');
```

> Se le colonne `is_public` / `acknowledged_at` non esistessero ancora in produzione, aggiungerle con:
> ```sql
> ALTER TABLE `referrals`
>   ADD COLUMN `is_public` TINYINT(1) NOT NULL DEFAULT 0 AFTER `outcome`,
>   ADD COLUMN `acknowledged_at` TIMESTAMP NULL AFTER `is_public`;
> ```

In locale è sufficiente `php artisan migrate`.
