<?php

namespace App\Enums;

enum ReferralStatus: string
{
    // ── Ciclo di vita corrente ─────────────────────────────────────────────
    case Sent            = 'sent';             // Inviata: segnalatore collega cliente ↔ professionista
    case InProgress      = 'in_progress';      // Presa in carico / consulenza in corso
    case Completed       = 'completed';        // Valore dichiarato dal professionista, in attesa conferma cliente
    case ClientConfirmed = 'client_confirmed'; // Cliente ha confermato il servizio, in attesa validazione admin
    case Confirmed       = 'confirmed';        // Valore validato dall'admin → conta per classifica e premi
    case Cancelled       = 'cancelled';        // Annullata: non andata a buon fine
    case Rejected        = 'rejected';         // Valore dichiarato NON validato dall'admin

    // ── Stati storici (retrocompatibilità con i dati esistenti) ────────────
    case InCharge    = 'in_charge';
    case Contacted   = 'contacted';
    case Negotiating = 'negotiating';
    case Won         = 'won';
    case Lost        = 'lost';
    case Archived    = 'archived';

    /**
     * Etichetta tradotta (bilingue via lang/{it,en}/referrals.php).
     */
    public function label(): string
    {
        return match ($this) {
            self::Sent                                          => __('referrals.status.sent'),
            self::InProgress,
            self::InCharge, self::Contacted, self::Negotiating  => __('referrals.status.in_progress'),
            self::Completed                                     => __('referrals.status.completed'),
            self::ClientConfirmed                               => __('referrals.status.client_confirmed'),
            self::Confirmed, self::Won                          => __('referrals.status.confirmed'),
            self::Cancelled, self::Lost, self::Archived         => __('referrals.status.cancelled'),
            self::Rejected                                      => __('referrals.status.rejected'),
        };
    }

    /**
     * Solo gli stati selezionabili nelle UI (esclude gli stati storici).
     */
    public static function options(): array
    {
        return collect(self::currentCases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }

    /**
     * Stati del nuovo ciclo di vita (senza alias storici).
     *
     * @return array<int, self>
     */
    public static function currentCases(): array
    {
        return [
            self::Sent,
            self::InProgress,
            self::Completed,
            self::ClientConfirmed,
            self::Confirmed,
            self::Cancelled,
            self::Rejected,
        ];
    }

    /**
     * La referenza è "aperta": lavoro ancora in corso.
     */
    public function isOpen(): bool
    {
        return in_array($this, [
            self::Sent, self::InProgress,
            self::InCharge, self::Contacted, self::Negotiating,
        ], true);
    }

    /**
     * In attesa di una conferma (cliente o admin), ma non ancora chiusa.
     */
    public function isPending(): bool
    {
        return in_array($this, [self::Completed, self::ClientConfirmed], true);
    }

    /**
     * Stato finale (non più modificabile dal flusso ordinario).
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::Confirmed, self::Cancelled, self::Rejected,
            self::Won, self::Lost, self::Archived,
        ], true);
    }

    /**
     * Il valore concorre alla classifica e ai premi solo se confermato dall'admin.
     */
    public function countsForScore(): bool
    {
        return $this === self::Confirmed || $this === self::Won;
    }

    /**
     * Classe CSS per il badge nella UI membri (.kr-status-*).
     */
    public function colorClass(): string
    {
        return match (true) {
            $this === self::Sent            => 'kr-status-blue',
            $this->isOpen()                 => 'kr-status-green',
            $this->countsForScore()         => 'kr-status-won',
            $this === self::Completed       => 'kr-status-amber',
            $this === self::ClientConfirmed => 'kr-status-teal',
            default                         => 'kr-status-red',
        };
    }

    /**
     * Colore badge nel pannello Filament.
     */
    public function filamentColor(): string
    {
        return match (true) {
            $this === self::Sent            => 'info',
            $this->isOpen()                 => 'warning',
            $this === self::Completed       => 'warning',
            $this === self::ClientConfirmed => 'info',
            $this->countsForScore()         => 'success',
            default                         => 'danger',
        };
    }
}
