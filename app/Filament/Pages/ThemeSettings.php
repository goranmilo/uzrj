<?php

namespace App\Filament\Pages;

use App\Models\Podesavanje;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ThemeSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';
    protected static ?string $navigationGroup = 'Administracija';
    protected static ?string $navigationLabel = 'Tema aplikacije';
    protected static ?string $title = 'Podešavanje teme';
    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.theme-settings';

    public ?string $tema = 'emerald';
    public ?string $custom_primary = '#10B981';
    public ?string $custom_primary_dark = '#059669';
    public ?string $custom_accent = '#34D399';
    public bool $dark_mode = false;

    // Definisane teme
    protected static array $teme = [
        'emerald' => [
            'naziv' => 'Emerald (Zelena)',
            'opis' => 'Podrazumevana zelena tema',
            'primary' => '#10B981',
            'primary-dark' => '#059669',
            'accent' => '#34D399',
        ],
        'blue' => [
            'naziv' => 'Ocean (Plava)',
            'opis' => 'Plava tema - profesionalna',
            'primary' => '#3B82F6',
            'primary-dark' => '#2563EB',
            'accent' => '#60A5FA',
        ],
        'purple' => [
            'naziv' => 'Royal (Ljubičasta)',
            'opis' => 'Ljubičasta tema - elegantna',
            'primary' => '#8B5CF6',
            'primary-dark' => '#7C3AED',
            'accent' => '#A78BFA',
        ],
        'red' => [
            'naziv' => 'Crimson (Crvena)',
            'opis' => 'Crvena tema - energična',
            'primary' => '#EF4444',
            'primary-dark' => '#DC2626',
            'accent' => '#F87171',
        ],
        'orange' => [
            'naziv' => 'Sunset (Narandžasta)',
            'opis' => 'Narandžasta tema - topla',
            'primary' => '#F97316',
            'primary-dark' => '#EA580C',
            'accent' => '#FB923C',
        ],
        'teal' => [
            'naziv' => 'Teal (Tirkizna)',
            'opis' => 'Tirkizna tema - moderna',
            'primary' => '#14B8A6',
            'primary-dark' => '#0D9488',
            'accent' => '#2DD4BF',
        ],
        'pink' => [
            'naziv' => 'Rose (Roze)',
            'opis' => 'Roze tema - nežna',
            'primary' => '#EC4899',
            'primary-dark' => '#DB2777',
            'accent' => '#F472B6',
        ],
        'dark' => [
            'naziv' => 'Dark (Tamna)',
            'opis' => 'Tamna tema - za noćni rad',
            'primary' => '#6366F1',
            'primary-dark' => '#4F46E5',
            'accent' => '#818CF8',
            'dark_mode' => true,
        ],
    ];

    public function mount(): void
    {
        $this->tema = Podesavanje::get('tema', 'emerald');
        $this->custom_primary = Podesavanje::get('tema_primary', '#10B981');
        $this->custom_primary_dark = Podesavanje::get('tema_primary_dark', '#059669');
        $this->custom_accent = Podesavanje::get('tema_accent', '#34D399');
        $this->dark_mode = Podesavanje::get('tema_dark_mode', false);

        $this->form->fill([
            'tema' => $this->tema,
            'custom_primary' => $this->custom_primary,
            'custom_primary_dark' => $this->custom_primary_dark,
            'custom_accent' => $this->custom_accent,
            'dark_mode' => $this->dark_mode,
        ]);
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Izbor teme')
                ->schema([
                    Forms\Components\Radio::make('tema')
                        ->label('Predefinisane teme')
                        ->options(fn (): array => 
                            collect(static::$teme)->mapWithKeys(fn ($t, $key) => [
                                $key => $t['naziv'],
                            ])->toArray()
                        )
                        ->descriptions(fn (): array => 
                            collect(static::$teme)->mapWithKeys(fn ($t, $key) => [
                                $key => $t['opis'],
                            ])->toArray()
                        )
                        ->reactive()
                        ->columns(2),
                ]),

            Forms\Components\Section::make('Prilagođene boje')
                ->schema([
                    Forms\Components\ColorPicker::make('custom_primary')
                        ->label('Primarna boja')
                        ->nullable(),
                    Forms\Components\ColorPicker::make('custom_primary_dark')
                        ->label('Primarna boja (tamna)')
                        ->nullable(),
                    Forms\Components\ColorPicker::make('custom_accent')
                        ->label('Akcent boja')
                        ->nullable(),
                    Forms\Components\Toggle::make('dark_mode')
                        ->label('Tamni režim')
                        ->helperText('Omogućava tamnu pozadinu aplikacije'),
                ])
                ->columns(3)
                ->collapsible(),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Podesavanje::set('tema', $data['tema'], 'string');
        Podesavanje::set('tema_primary', $data['custom_primary'] ?? '#10B981', 'string');
        Podesavanje::set('tema_primary_dark', $data['custom_primary_dark'] ?? '#059669', 'string');
        Podesavanje::set('tema_accent', $data['custom_accent'] ?? '#34D399', 'string');
        Podesavanje::set('tema_dark_mode', $data['dark_mode'] ?? false, 'boolean');

        Notification::make()
            ->title('Tema sačuvana')
            ->body('Promene će biti primenjene nakon osvežavanja stranice.')
            ->success()
            ->send();
    }

    public static function getTeme(): array
    {
        return static::$teme;
    }

    public static function getAktuelnaTema(): array
    {
        $temaKey = Podesavanje::get('tema', 'emerald');
        $tema = static::$teme[$temaKey] ?? static::$teme['emerald'];

        // Custom boje imaju prioritet
        $customPrimary = Podesavanje::get('tema_primary');
        if ($customPrimary) {
            $tema['primary'] = $customPrimary;
        }

        $customPrimaryDark = Podesavanje::get('tema_primary_dark');
        if ($customPrimaryDark) {
            $tema['primary-dark'] = $customPrimaryDark;
        }

        $customAccent = Podesavanje::get('tema_accent');
        if ($customAccent) {
            $tema['accent'] = $customAccent;
        }

        $tema['dark_mode'] = Podesavanje::get('tema_dark_mode', false);

        return $tema;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }
}
