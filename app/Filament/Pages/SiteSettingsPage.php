<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Support\MemberNavigation;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;

/**
 * @property-read Schema $form
 */
class SiteSettingsPage extends Page
{
    protected static ?string $navigationLabel = 'Menu area utenti';
    protected static ?string $title = 'Impostazioni sito';
    protected static string|\UnitEnum|null $navigationGroup = 'Sito';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3;
    protected static ?int $navigationSort = 11;
    protected string $view = 'filament.pages.site-settings';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'registration_headline'    => SiteSetting::get('registration_headline', 'Entra in Kommunity'),
            'registration_subheadline' => SiteSetting::get('registration_subheadline', 'Kommunity: la piattaforma che fa crescere il tuo business'),
            'registration_body'        => SiteSetting::get('registration_body'),
            'member_navigation_items'  => MemberNavigation::enabledKeys(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Pagina di registrazione')
                    ->description('Testo mostrato ai visitatori sulla pagina di registrazione, accanto al form di iscrizione.')
                    ->schema([
                        TextInput::make('registration_headline')
                            ->label('Titolo principale')
                            ->placeholder('Entra in Kommunity')
                            ->maxLength(120),

                        TextInput::make('registration_subheadline')
                            ->label('Sottotitolo')
                            ->placeholder('Kommunity: la piattaforma che fa crescere il tuo business')
                            ->maxLength(200),

                        RichEditor::make('registration_body')
                            ->label('Corpo del testo (perché iscriversi)')
                            ->helperText('Spiega i vantaggi di iscriversi. Supporta grassetto, corsivo, elenchi e link.')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline',
                                'bulletList', 'orderedList',
                                'h2', 'h3',
                                'link',
                                'undo', 'redo',
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('Menu area utenti')
                    ->description('Scegli quali voci mostrare nel menu superiore dell’area riservata. Le pagine restano raggiungibili via URL, ma non appaiono nel menu.')
                    ->schema([
                        CheckboxList::make('member_navigation_items')
                            ->label('Voci visibili nel menu in alto')
                            ->options(fn (): array => MemberNavigation::options())
                            ->columns(2)
                            ->bulkToggleable()
                            ->helperText('Deseleziona una voce per nasconderla dal menu superiore e dal menu mobile.'),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment(Alignment::Start)
                    ->key('form-actions'),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        SiteSetting::set('registration_headline',    $data['registration_headline'] ?? null);
        SiteSetting::set('registration_subheadline', $data['registration_subheadline'] ?? null);
        SiteSetting::set('registration_body',        $data['registration_body'] ?? null);
        SiteSetting::set(
            MemberNavigation::SETTING_KEY,
            json_encode(array_values($data['member_navigation_items'] ?? []))
        );

        Notification::make()
            ->title('Impostazioni salvate con successo')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Salva impostazioni')
                ->submit('save'),
        ];
    }
}
