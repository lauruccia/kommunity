<?php

namespace App\Filament\Resources\Chapters\RelationManagers;

use App\Models\MemberProfile;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MemberProfilesRelationManager extends RelationManager
{
    protected static string $relationship = 'memberProfiles';
    protected static ?string $title = 'Membri del Pianeta';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Membro')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('profession.name')
                    ->label('Professione')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('profession_other')
                    ->label('Professione (altro)')
                    ->placeholder('-'),
                TextColumn::make('categories.name')
                    ->label('Categorie')
                    ->badge()
                    ->separator(', ')
                    ->placeholder('-'),
                TextColumn::make('city.name')
                    ->label('Città')
                    ->placeholder('-'),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
            ])
            ->filters([])
            ->headerActions([
                // Aggiunge un membro esistente al Pianeta
                \Filament\Actions\CreateAction::make()
                    ->label('Aggiungi membro')
                    ->modalHeading('Assegna membro al Pianeta')
                    ->form([
                        Select::make('member_profile_id')
                            ->label('Membro')
                            ->options(
                                MemberProfile::query()
                                    ->whereNull('active_chapter_id')
                                    ->with('user')
                                    ->get()
                                    ->mapWithKeys(fn ($p) => [$p->id => $p->user->name.' ('.$p->user->email.')'])
                            )
                            ->searchable()
                            ->required(),
                    ])
                    ->using(function (array $data): MemberProfile {
                        $profile = MemberProfile::findOrFail($data['member_profile_id']);
                        $profile->update(['active_chapter_id' => $this->getOwnerRecord()->id]);
                        return $profile;
                    })
                    ->successNotificationTitle('Membro aggiunto al Pianeta'),
            ])
            ->recordActions([
                // Rimuove il membro dal Pianeta (senza eliminarlo)
                \Filament\Actions\Action::make('rimuovi')
                    ->label('Rimuovi dal Pianeta')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rimuovere il membro dal Pianeta?')
                    ->modalDescription('Il membro non sarà eliminato, ma verrà rimosso da questo Pianeta.')
                    ->action(fn (MemberProfile $record) => $record->update(['active_chapter_id' => null]))
                    ->successNotificationTitle('Membro rimosso dal Pianeta'),
            ])
            ->bulkActions([]);
    }
}
