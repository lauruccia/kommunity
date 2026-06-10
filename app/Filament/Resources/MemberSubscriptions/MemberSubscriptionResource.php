<?php

namespace App\Filament\Resources\MemberSubscriptions;

use App\Enums\PaymentMethod;
use App\Enums\SubscriptionStatus;
use App\Filament\Resources\MemberSubscriptions\Pages\CreateMemberSubscription;
use App\Filament\Resources\MemberSubscriptions\Pages\EditMemberSubscription;
use App\Filament\Resources\MemberSubscriptions\Pages\ListMemberSubscriptions;
use App\Models\MemberSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Collection;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use App\Notifications\SubscriptionApprovedNotification;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MemberSubscriptionResource extends Resource
{
    protected static ?string $model = MemberSubscription::class;
    protected static ?string $navigationLabel = 'Abbonamenti membri';
    protected static ?string $modelLabel = 'abbonamento';
    protected static ?string $pluralModelLabel = 'abbonamenti membri';
    protected static string|\UnitEnum|null $navigationGroup = 'Abbonamenti';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'admin-community']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('Membro')
                ->relationship('user', 'name')
                ->searchable()
                ->preload()
                ->required(),

            Select::make('plan_id')
                ->label('Piano')
                ->options(SubscriptionPlan::query()->pluck('name', 'id'))
                ->required(),

            Select::make('status')
                ->label('Stato')
                ->options(collect(SubscriptionStatus::cases())->mapWithKeys(
                    fn ($case) => [$case->value => $case->label()]
                ))
                ->required()
                ->default(SubscriptionStatus::Pending->value),

            Select::make('payment_method')
                ->label('Metodo pagamento')
                ->options(collect(PaymentMethod::cases())->mapWithKeys(
                    fn ($case) => [$case->value => $case->label()]
                ))
                ->nullable(),

            TextInput::make('payment_reference')
                ->label('Riferimento pagamento (es. CRO, ID transazione)')
                ->maxLength(255),

            Textarea::make('payment_notes')
                ->label('Note membro')
                ->rows(2),

            DateTimePicker::make('trial_ends_at')
                ->label('Fine periodo prova'),

            DateTimePicker::make('starts_at')
                ->label('Data inizio'),

            DateTimePicker::make('ends_at')
                ->label('Data scadenza (vuoto = nessuna scadenza)'),

            Textarea::make('admin_notes')
                ->label('Note interne admin')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Membro')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('plan.name')
                    ->label('Piano')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof SubscriptionStatus ? $state->label() : $state)
                    ->color(fn ($state) => match(true) {
                        $state instanceof SubscriptionStatus => $state->color(),
                        default => 'gray',
                    }),

                TextColumn::make('payment_method')
                    ->label('Pagamento')
                    ->formatStateUsing(fn ($state) => $state instanceof PaymentMethod ? $state->label() : ($state ?? '—')),

                TextColumn::make('starts_at')
                    ->label('Inizio')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label('Scadenza')
                    ->dateTime('d/m/Y')
                    ->default('—')
                    ->sortable(),

                TextColumn::make('requested_at')
                    ->label('Richiesto il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('requested_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options(collect(SubscriptionStatus::cases())->mapWithKeys(
                        fn ($case) => [$case->value => $case->label()]
                    )),

                SelectFilter::make('plan_id')
                    ->label('Piano')
                    ->options(SubscriptionPlan::query()->pluck('name', 'id')),
            ])
            ->recordActions([
                // Approva
                Action::make('approve')
                    ->label('Approva')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->status, [
                        SubscriptionStatus::Pending,
                        SubscriptionStatus::Trial,
                    ]))
                    ->form([
                        DateTimePicker::make('starts_at')
                            ->label('Data inizio')
                            ->default(now())
                            ->required(),
                        DateTimePicker::make('ends_at')
                            ->label('Scadenza (vuoto = nessuna)')
                            ->nullable(),
                        Textarea::make('admin_notes')
                            ->label('Note interne (opzionale)')
                            ->rows(2),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'status'      => SubscriptionStatus::Active,
                            'starts_at'   => $data['starts_at'],
                            'ends_at'     => $data['ends_at'] ?? null,
                            'admin_notes' => $data['admin_notes'] ?? $record->admin_notes,
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                        // Notifica il membro via email
                        $record->refresh();
                        $record->user?->notify(new SubscriptionApprovedNotification($record));
                        Notification::make()->title('Abbonamento approvato')->success()->send();
                    }),

                // Rifiuta
                Action::make('reject')
                    ->label('Rifiuta')
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === SubscriptionStatus::Pending)
                    ->form([
                        Textarea::make('admin_notes')
                            ->label('Motivo rifiuto (visibile nelle note interne)')
                            ->rows(2),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'status'      => SubscriptionStatus::Rejected,
                            'admin_notes' => $data['admin_notes'] ?? null,
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                        Notification::make()->title('Abbonamento rifiutato')->warning()->send();
                    }),

                // Attiva prova
                Action::make('start_trial')
                    ->label('Avvia prova')
                    ->icon(Heroicon::OutlinedBeaker)
                    ->color('info')
                    ->visible(fn ($record) => $record->status === SubscriptionStatus::Pending && $record->plan?->hasTrial())
                    ->form([
                        DateTimePicker::make('trial_ends_at')
                            ->label('Fine periodo di prova')
                            ->default(fn ($record) => now()->addDays($record->plan?->trial_days ?? 14))
                            ->required(),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'status'        => SubscriptionStatus::Trial,
                            'trial_ends_at' => $data['trial_ends_at'],
                            'approved_by'   => auth()->id(),
                            'approved_at'   => now(),
                        ]);
                        Notification::make()->title('Periodo di prova avviato')->info()->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('exportCsv')
                        ->label('Esporta CSV')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->color('info')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records): \Symfony\Component\HttpFoundation\StreamedResponse {
                            $filename = 'abbonamenti_' . now()->format('Ymd_His') . '.csv';
                            return response()->streamDownload(function () use ($records): void {
                                $handle = fopen('php://output', 'w');
                                fputcsv($handle, ['ID', 'Membro', 'Email', 'Piano', 'Stato', 'Metodo pagamento', 'Riferimento', 'Inizio', 'Scadenza', 'Richiesto il', 'Approvato il', 'Note admin']);
                                foreach ($records as $sub) {
                                    fputcsv($handle, [
                                        $sub->id,
                                        $sub->user?->name ?? '',
                                        $sub->user?->email ?? '',
                                        $sub->plan?->name ?? '',
                                        $sub->status instanceof SubscriptionStatus ? $sub->status->label() : ($sub->status ?? ''),
                                        $sub->payment_method instanceof PaymentMethod ? $sub->payment_method->label() : ($sub->payment_method ?? ''),
                                        $sub->payment_reference ?? '',
                                        $sub->starts_at?->format('d/m/Y') ?? '',
                                        $sub->ends_at?->format('d/m/Y') ?? 'Nessuna scadenza',
                                        $sub->requested_at?->format('d/m/Y H:i') ?? '',
                                        $sub->approved_at?->format('d/m/Y H:i') ?? '',
                                        $sub->admin_notes ?? '',
                                    ]);
                                }
                                fclose($handle);
                            }, $filename, ['Content-Type' => 'text/csv']);
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListMemberSubscriptions::route('/'),
            'create' => CreateMemberSubscription::route('/create'),
            'edit'   => EditMemberSubscription::route('/{record}/edit'),
        ];
    }
}
