<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ClanarinaPeriodResource;
use App\Filament\Resources\ClanarinaResource;
use App\Filament\Resources\ClanResource;
use App\Filament\Resources\EdukacijaResource;
use App\Filament\Resources\UplataResource;
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
        ] + array_map(
            fn (string $resurs): array => $resurs::izabraneKolone(),
            static::listeSaIzboromKolona(),
        ));
    }

    /**
     * Polja se vezuju za `$data`; bez toga Livewire nema svojstvo za vezivanje
     * (klasa nema public property po ključu podešavanja).
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
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

            Forms\Components\Section::make('Prikaz liste članova')
                ->description('Kolone koje se prikazuju na stranici Članstvo → Članovi.')
                ->schema([
                    $this->izborKolona(ClanResource::class, 'Kolone'),
                ]),

            Forms\Components\Section::make('Prikaz liste edukacija')
                ->description('Kolone koje se prikazuju na stranici Edukacije → Edukacije.')
                ->schema([
                    $this->izborKolona(EdukacijaResource::class, 'Kolone'),
                ]),

            Forms\Components\Section::make('Prikaz lista u finansijama')
                ->description('Kolone koje se prikazuju na stranicama Finansije → Članarine, Uplate i Periodi članarine.')
                ->schema([
                    $this->izborKolona(ClanarinaResource::class, 'Članarine'),
                    $this->izborKolona(UplataResource::class, 'Uplate'),
                    $this->izborKolona(ClanarinaPeriodResource::class, 'Periodi članarine'),
                ]),

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

    /**
     * Resursi čije kolone admin bira, po ključu podešavanja.
     *
     * @return array<string, class-string>
     */
    protected static function listeSaIzboromKolona(): array
    {
        return collect([
            ClanResource::class,
            EdukacijaResource::class,
            ClanarinaResource::class,
            UplataResource::class,
            ClanarinaPeriodResource::class,
        ])->mapWithKeys(fn (string $resurs): array => [$resurs::kljucPodesavanjaKolona() => $resurs])->all();
    }

    /**
     * Izbor kolona za listu resursa (vidi {@see \App\Filament\Concerns\ImaIzborKolona}).
     */
    protected function izborKolona(string $resurs, string $label): Forms\Components\CheckboxList
    {
        return Forms\Components\CheckboxList::make($resurs::kljucPodesavanjaKolona())
            ->label($label)
            ->options(
                collect($resurs::dostupneKolone())
                    ->map(fn (array $kolona): string => $kolona['label'])
                    ->all()
            )
            ->columns(3)
            ->bulkToggleable()
            ->minItems(1)
            ->required()
            ->helperText('Redosled kolona je fiksan; prikazuju se samo označene.');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Čuvanje svih podešavanja
        foreach ($data as $kljuc => $vrednost) {
            $tip = match ($kljuc) {
                'godisnji_prag_bodova', 'ukupan_prag_bodova', 'licencni_period_god', 'dani_pre_isteka_upozorenje' => 'integer',
                'pro_rata_racunanje' => 'boolean',
                default => str_ends_with($kljuc, '_kolone') ? 'json' : 'string',
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
