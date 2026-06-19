<?php

namespace App\Enums;

enum ReferralStatus: string
{
    // ── Ciclo di vita corrente ─────────────────────────────────────────────
    case Sent       = 'sent';        // Inviata dal segnalatore al professionista
    case InProgress = 'in_progress'; // Presa in carico / consulenza in corso
    case Completed  = 'completed';   // Consulenza conclusa: valore dichiarato, in attesa di validazione
    case Confirmed  = 'confirmed';   // Valore validato dall'admin → conta per classifica e premi
    case Cancelled  = 'cancelled';   // Annullata: non andata a buon fine
    case Rejected   = 'rejected';    // Valore dichiarato NON validato dall'admin

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
            self::Confirmed,
            self::Cancelled,
            self::Rejected,
        ];
    }

    /**
     * La referenza è "aperta" (in lavorazione, non chiusa).
     */
    public function isOpen(): bool
    {
        return in_array($this, [
            self::Sent, self::InProgress,
            self::InCharge, self::Contacted, self::Negotiating,
        ], true);
    }

    /**
     * Il valore è stato dichiarato dal professionista ma è in attesa di validazione admin.
     */
    public function isAwaitingValidation(): bool
    {
        return $this === self::Completed;
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
            $this === self::Sent                          => 'kr-status-blue',
            $this->isOpen()                               => 'kr-status-green',
            $this->countsForScore()                       => 'kr-status-won',
            $this === self::Completed                     => 'kr-status-amber',
            $this === self::Cancelled, $this === self::Rejected,
            $this === self::Lost, $this === self::Archived => 'kr-status-red',
            default                                        => 'kr-status-slate',
        };
    }

    /**
     * Colore badge nel pannello Filament.
     */
    public function filamentColor(): string
    {
        return match (true) {
            $this === self::Sent       => 'info',
            $this->isOpen()            => 'warning',
            $this === self::Completed  => 'warning',
            $this->countsForScore()    => 'success',
            default                    => 'danger',
        };
    }
}
