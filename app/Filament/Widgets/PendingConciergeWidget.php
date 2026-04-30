<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Services\Features;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/**
 * Widget Filament: lista nuovi membri da contattare in concierge entro 24h.
 *
 * Visibile solo se la feature flag `concierge_onboarding` è attiva e
 * l'utente loggato ha role super-admin o admin-community.
 */
class PendingConciergeWidget extends BaseWidget
{
    protected static ?string $heading = 'Concierge onboarding · da contattare';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        if (! Features::enabled('concierge_onboarding')) {
            return false;
        }

        $user = auth()->user();

        return $user?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->whereNotNull('concierge_assigned_at')
                    ->whereNull('concierge_completed_at')
                    ->latest('concierge_assigned_at')
                    ->limit(20)
            )
            ->emptyStateHeading('Nessun nuovo membro da contattare 🎉')
            ->columns([
                TextColumn::make('name')->label('Membro')->searchable()->weight('bold'),
                TextColumn::make('email')->label('Email')->copyable()->size('xs'),
                TextColumn::make('memberProfile.chapter.name')->label('Capitolo')->placeholder('—'),
                TextColumn::make('invitedBy.name')->label('Invitato da')->placeholder('—'),
                TextColumn::make('concierge_assigned_at')
                    ->label('Iscritto')
                    ->since()
                    ->tooltip(fn ($record) => $record->concierge_assigned_at?->format('d/m/Y H:i')),
                TextColumn::make('hours_left')
                    ->label('Tempo residuo')
                    ->state(function ($record) {
                        if (! $record->concierge_assigned_at) {
                            return null;
                        }
                        $hoursElapsed = (int) $record->concierge_assigned_at->diffInHours(now());
                        $left         = max(0, 24 - $hoursElapsed);
                        return $left . 'h';
                    })
                    ->badge()
                    ->color(fn (string $state): string => str_replace('h', '', $state) <= '4' ? 'danger' : 'success'),
            ])
            ->recordActions([
                Action::make('mark_completed')
                    ->label('Segna completato')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->forceFill([
                            'concierge_completed_at' => now(),
                            'concierge_assigned_to'  => auth()->id(),
                        ])->saveQuietly();
                    }),
                Action::make('open_user')
                    ->label('Apri scheda')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (User $record): string => '/admin/users/' . $record->getKey() . '/edit'),
            ]);
    }
}
