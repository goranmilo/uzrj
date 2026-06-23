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

class SystemConfiguration extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Administracija';
    protected static ?string $navigationLabel = 'Konfiguracija sistema';
    protected static ?string $title = 'Konfiguracija sistema';
    protected static ?int $navigationSort = 19;

    protected static string $view = 'filament.pages.system-configuration';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'godisnji_prag_bodova' => Podesavanje::get('godisnji_prag_bodova', 20),
            'ukupan_prag_bodova' => Podesavanje::get('ukupan_prag_bodova', 140),
            'licencni_period_god' => Podesavanje::get('licencni_period_god', 7),
            'dani_pre_isteka_upozorenje' => Podesavanje::get('dani_pre_isteka_upozorenje', 60),
            'vrsta_naplate_clanarine' => Podesavanje::get('vrsta_naplate_clanarine', 'godisnje'),
            'pro_rata_racunanje' => Podesavanje::get('pro_rata_racunanje', true),
            'naziv_udruzenja' => Podesavanje::get('naziv_udruzenja', ''),
            'maticni_broj_udruzenja' => Podesavanje::get('maticni_broj_udruzenja', ''),
            'adresa_udruzenja' => Podesavanje::get('adresa_udruzenja', ''),
            'email_udruzenja' => Podesavanje::get('email_udruzenja', ''),
            'telefon_udruzenja' => Podesavanje::get('telefon_udruzenja', ''),
        ]);
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Bodovni sistem')
                ->schema([
                    Forms\Components\TextInput::make('godisnji_prag_bodova')
                        ->label('Godišnji prag bodova')
                        ->numeric()
                        ->required()
                        ->helperText('Minimalan broj bodova koje član treba da sakupi u toku godine'),
                    Forms\Components\TextInput::make('ukupan_prag_bodova')
                        ->label('Ukupan prag za licencni period')
                        ->numeric()
                        ->required()
                        ->helperText('Ukupan broj bodova za obnovu licence'),
                    Forms\Components\TextInput::make('licencni_period_god')
                        ->label('Licencni period (godina)')
                        ->numeric()
                        ->required()
                        ->helperText('Trajanje licence u godinama'),
                    Forms\Components\TextInput::make('dani_pre_isteka_upozorenje')
                        ->label('Upozorenje pre isteka (dana)')
                        ->numeric()
                        ->required()
                        ->helperText('Koliko dana pre isteka licence se šalje upozorenje'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Članarina')
                ->schema([
                    Forms\Components\Select::make('vrsta_naplate_clanarine')
                        ->label('Vrsta naplate')
                        ->options([
                            'godisnje' => 'Godišnje',
                            'mesecno' => 'Mesečno',
                            'kvartalno' => 'Kvartalno',
                        ])
                        ->required(),
                    Forms\Components\Toggle::make('pro_rata_racunanje')
                        ->label('Pro-rata obračun')
                        ->helperText('Da li se obračunava srazmerno za članove koji se učlane usred perioda'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Podaci o udruženju')
                ->schema([
                    Forms\Components\TextInput::make('naziv_udruzenja')
                        ->label('Naziv udruženja')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('maticni_broj_udruzenja')
                        ->label('Matični broj')
                        ->maxLength(50),
                    Forms\Components\TextInput::make('adresa_udruzenja')
                        ->label('Adresa')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email_udruzenja')
                        ->label('Email')
                        ->email()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('telefon_udruzenja')
                        ->label('Telefon')
                        ->maxLength(50),
                ])
                ->columns(2),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Čuvanje svih podešavanja
        foreach ($data as $kljuc => $vrednost) {
            $tip = match($kljuc) {
                'godisnji_prag_bodova', 'ukupan_prag_bodova', 'licencni_period_god', 'dani_pre_isteka_upozorenje' => 'integer',
                'pro_rata_racunanje' => 'boolean',
                default => 'string',
            };

            Podesavanje::set($kljuc, $vrednost, $tip);
        }

        Notification::make()
            ->title('Konfiguracija sačuvana')
            ->body('Sva podešavanja su uspešno ažurirana.')
            ->success()
            ->send();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }
}
