<?php

namespace App\Filament\Widgets;

use App\Enums\SubscriptionStatus;
use App\Models\ChapterJoinRequest;
use App\Models\MemberSubscription;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Widget dashboard: abbonamenti e iscrizioni in attesa di approvazione.
 * Mostra le 10 richieste più recenti aggregate.
 * Visibile solo a super-admin e admin-community.
 */
class PendingApprovalsWidget extends BaseWidget
{
    protected static ?string $heading = 'Abbonamenti in attesa di approvazione';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MemberSubscription::query()
                    ->where('status', SubscriptionStatus::Pending)
                    ->with(['user', 'plan'])
                    ->latest('requested_at')
                    ->limit(10)
            )
            ->emptyStateHeading('Nessun abbonamento in attesa 🎉')
            ->emptyStateDescription('Tutte le richieste sono state elaborate.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Utente')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->size('xs')
                    ->copyable(),
                TextColumn::make('plan.name')
                    ->label('Piano')
                    ->badge()
                    ->color('info'),
                TextColumn::make('payment_method')
                    ->label('Pagamento')
                    ->formatStateUsing(fn ($state) => $state?->label() ?? '-'),
                TextColumn::make('payment_reference')
                    ->label('Riferimento')
                    ->placeholder('-')
                    ->size('xs'),
                TextColumn::make('requested_at')
                    ->label('Richiesto')
                    ->since()
                    ->tooltip(fn ($record) => $record->requested_at?->format('d/m/Y H:i') ?? '-'),
            ])
            ->recordActions([
                Action::make('approva')
                    ->label('Approva')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approvare questo abbonamento?')
                    ->action(function (MemberSubscription $record): void {
                        $record->update([
                            'status'      => SubscriptionStatus::Active,
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                            'starts_at'   => now(),
                        ]);
                    }),
                Action::make('rifiuta')
                    ->label('Rifiuta')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rifiutare questo abbonamento?')
                    ->action(function (MemberSubscription $record): void {
                        $record->update([
                            'status' => SubscriptionStatus::Rejected,
                        ]);
                    }),
                Action::make('apri')
                    ->label('Scheda completa')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (MemberSubscription $record): string => '/admin/member-subscriptions/' . $record->getKey() . '/edit'),
            ]);
    }
}
