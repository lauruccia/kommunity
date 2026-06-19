# Referenze 2.0 — Tre attori, valore generato e premi

Proposta e implementazione della revisione del sistema **Referenze**.

## 1. I tre attori

| Attore | Ruolo | Esempio |
|--------|-------|---------|
| **Segnalatore** | Crea la segnalazione, collega cliente e professionista | Francesco |
| **Professionista** | Riceve il lavoro, esegue la consulenza, dichiara il valore | il commercialista |
| **Cliente segnalato** | Il membro che ha bisogno del servizio, conferma di averlo ricevuto | Fabbro |

Tutti e tre sono **membri della Kommunity** e hanno una propria vista sullo stato della referenza.

> Francesco collega Fabbro (che ha bisogno di un commercialista) al commercialista della Kommunity.
> Sia il commercialista sia Fabbro vengono avvisati. A lavoro concluso il commercialista dichiara «consulenza da 1.000 €», Fabbro conferma di averla ricevuta, l'admin valida, e Francesco prende i punti.

## 2. Decisioni di prodotto adottate

| Tema | Scelta |
|------|--------|
| Chi riceve i punti/premi | **Il segnalatore** |
| Validazione del valore | **Approvazione admin** |
| Cliente segnalato | **Membro Kommunity** (vista + notifiche) |
| Destinatario della segnalazione | **Entrambi** (professionista + cliente avvisati) |
| Conferma del servizio | **Sì, il cliente conferma** prima della validazione admin |

## 3. Ciclo di vita degli stati

| Stato | Significato | Chi agisce |
|-------|-------------|-----------|
| **Inviata** (`sent`) | Segnalatore collega cliente ↔ professionista | Segnalatore |
| **In corso** (`in_progress`) | Il professionista ha preso in carico | Professionista |
| **Valore dichiarato** (`completed`) | Importo dichiarato, in attesa di conferma cliente | Professionista |
| **Confermata dal cliente** (`client_confirmed`) | Il cliente ha confermato il servizio, in attesa admin | Cliente |
| **Confermata** (`confirmed`) | Valore validato → **conta per classifica e premi** | Admin |
| **Annullata** (`cancelled`) | Non andata a buon fine | Professionista/Admin |
| **Valore rifiutato** (`rejected`) | Importo non approvato | Admin |

Migrazione automatica dei dati storici: `in_charge/contacted/negotiating → in_progress`, `won → confirmed`, `lost/archived → cancelled`.

## 4. Funzioni per attore

| Funzione | Segnalatore | Professionista | Cliente | Admin |
|---|:--:|:--:|:--:|:--:|
| Crea la referenza (sceglie professionista **e** cliente) | ✅ | | | |
| Riceve notifica alla creazione | | ✅ | ✅ | |
| Prende in carico / contatta | | ✅ | | |
| Dichiara il valore | | ✅ | | |
| Conferma il servizio ricevuto | | | ✅ | |
| Valida il valore | | | | ✅ |
| Vede lo stato | ✅ (tab *Inviate*) | ✅ (tab *Ricevute*) | ✅ (tab *Sono stato segnalato*) | ✅ (Moderazione + Filament) |
| Riceve i punti/premi | ✅ | | | |

## 5. Il flusso completo

1. **Segnalo** → il segnalatore sceglie **professionista** e **cliente**, crea la referenza (*Inviata*). Professionista e cliente vengono notificati.
2. **Prende in carico** → il professionista (*In corso*).
3. **Dichiara il valore** → il professionista inserisce l'importo (*Valore dichiarato*); cliente e segnalatore notificati.
4. **Conferma cliente** → il cliente clicca "Conferma servizio ricevuto" (*Confermata dal cliente*); segnalatore e admin notificati.
5. **Validazione admin** → l'admin **Approva** (*Confermata*, entra in classifica) o **Rifiuta** (*Valore rifiutato*).
6. **Classifica** → i punti del segnalatore si aggiornano.

## 6. Dati: colonne su `referrals`

| Colonna | Tipo | Uso |
|---------|------|-----|
| `client_user_id` | FK users | **Il cliente segnalato (membro)** |
| `declared_value` | DECIMAL(12,2) | Importo dichiarato dal professionista |
| `declared_at` | TIMESTAMP | Quando è stato dichiarato |
| `client_confirmed_at` | TIMESTAMP | Quando il cliente ha confermato |
| `approved_value` | DECIMAL(12,2) | Importo validato dall'admin (conta per i premi) |
| `approved_at` | TIMESTAMP | Quando è stato validato |
| `approved_by` | FK users | Admin che ha validato |

(`estimated_value`, `is_public`, `acknowledged_at` già presenti. `company_name`/`contact_name` restano come ripiego per clienti esterni.)

## 7. Sistema di punteggio e premi

Punti **al segnalatore**, solo per referenze in stato *Confermata*:

```
punti = (n. consulenze confermate × 50) + (valore confermato totale ÷ 10)
```

- **50 punti** per ogni consulenza andata a buon fine (premia il volume).
- **1 punto ogni 10 €** di valore validato (premia l'alto valore).

Esempio: 3 consulenze (1.000 + 5.000 + 2.000 €) = `3×50 + 8000/10` = **950 punti**.

Formula centralizzata in `ReferralScoreService` (costanti `PUNTI_BASE`, `EURO_PER_PUNTO`): base per definire i premi (soglie, classifica mensile/annuale via `approved_at`).

## 8. Dove si vede

- **Membri** (`/referenze`): form con **due menù** (professionista + cliente); tab **Ricevute / Inviate / 🙌 Sono stato segnalato / Archivio / 🏆 Classifica**; pulsanti "Dichiara valore" (professionista) e "Conferma servizio ricevuto" (cliente).
- **Admin** — tab *Moderazione*: Approva/Rifiuta sulle referenze da validare.
- **Filament** (`/admin`): colonne segnalatore/professionista/cliente/valori, filtro stato, azioni Approva/Rifiuta, badge col numero di referenze in attesa, widget Classifica in dashboard.

## 9. File toccati

**Backend:** `app/Enums/ReferralStatus.php`, `app/Models/Referral.php`, `app/Http/Controllers/ReferralController.php`, `app/Policies/ReferralPolicy.php`, `routes/web.php`, `app/Services/ReferralScoreService.php`, `database/migrations/2026_06_19_120000_add_referral_value_validation_columns.php`.

**Notifiche:** `ReferralClientReferredNotification`, `ReferralClientConfirmedNotification`, `ReferralValueDeclaredNotification`, `ReferralConfirmedNotification`.

**Admin/UI:** `app/Filament/Resources/Referrals/ReferralResource.php`, `app/Filament/Widgets/ReferralLeaderboardWidget.php`, `resources/views/referrals/index.blade.php`.

**Lingua:** `lang/{it,en}/referrals.php`, `lang/{it,en}/push.php`.

## 10. SQL per phpMyAdmin (produzione)

```sql
ALTER TABLE `referrals`
  ADD COLUMN `client_user_id`      BIGINT UNSIGNED NULL AFTER `recipient_id`,
  ADD COLUMN `declared_value`      DECIMAL(12,2)   NULL AFTER `estimated_value`,
  ADD COLUMN `declared_at`         TIMESTAMP       NULL AFTER `declared_value`,
  ADD COLUMN `client_confirmed_at` TIMESTAMP       NULL AFTER `declared_at`,
  ADD COLUMN `approved_value`      DECIMAL(12,2)   NULL AFTER `client_confirmed_at`,
  ADD COLUMN `approved_at`         TIMESTAMP       NULL AFTER `approved_value`,
  ADD COLUMN `approved_by`         BIGINT UNSIGNED NULL AFTER `approved_at`;

ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_client_user_id_foreign` FOREIGN KEY (`client_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `referrals_approved_by_foreign`    FOREIGN KEY (`approved_by`)     REFERENCES `users`(`id`) ON DELETE SET NULL;

-- Normalizzazione stati storici → nuovo ciclo di vita
UPDATE `referrals` SET `status`='in_progress' WHERE `status` IN ('in_charge','contacted','negotiating');
UPDATE `referrals` SET `approved_value`=`estimated_value`, `approved_at`=`updated_at`, `status`='confirmed' WHERE `status`='won';
UPDATE `referrals` SET `status`='cancelled' WHERE `status` IN ('lost','archived');
```

> Se mancassero `is_public` / `acknowledged_at`:
> ```sql
> ALTER TABLE `referrals`
>   ADD COLUMN `is_public` TINYINT(1) NOT NULL DEFAULT 0 AFTER `outcome`,
>   ADD COLUMN `acknowledged_at` TIMESTAMP NULL AFTER `is_public`;
> ```

In locale: `php artisan migrate`.

## 11. Nota sulle referenze esistenti

Le referenze già presenti non hanno un `client_user_id` (la colonna è nullable): restano valide ma senza cliente collegato. Il flusso a tre attori si applica alle **nuove** segnalazioni.
