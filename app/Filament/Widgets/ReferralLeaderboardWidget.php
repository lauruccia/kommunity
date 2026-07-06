<?php

namespace App\Filament\Widgets;

use App\Enums\ReferralStatus;
use App\Models\Referral;
use App\Services\ReferralScoreService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Classifica dei segnalatori per valore generato (referenze confermate).
 */
class ReferralLeaderboardWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): ?string
    {
        return 'Classifica referenze — valore generato';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Referral::query()
                    ->confirmed()
                    ->selectRaw('MIN(id) as id, sender_id, COUNT(*) as confirmed_count, COALESCE(SUM(COALESCE(approved_value, declared_value, 0)), 0) as total_value')
                    ->groupBy('sender_id')
                    ->orderByDesc('total_value')
                    ->orderBy('sender_id')
            )
            ->defaultKeySort(false)
            ->columns([
                TextColumn::make('sender.name')
                    ->label('Segnalatore')
                    ->default('—'),
                TextColumn::make('confirmed_count')
                    ->label('Consulenze confermate'),
                TextColumn::make('total_value')
                    ->label('Valore generato')
                    ->money('EUR'),
                TextColumn::make('points')
                    ->label('Punti')
                    ->state(fn (Referral $record): int => app(ReferralScoreService::class)
                        ->points((int) $record->confirmed_count, (float) $record->total_value))
                    ->badge()
                    ->color('success'),
            ])
            ->paginated([10, 25]);
    }
}
