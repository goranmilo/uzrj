<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\ImaIzborKolona;
use App\Filament\Resources\UplataResource\Pages;
use App\Models\Uplata;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UplataResource extends Resource
{
    use ImaIzborKolona;

    protected static ?string $model = Uplata::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Finansije';
    protected static ?string $navigationLabel = 'Uplate';
    protected static ?string $modelLabel = 'uplata';
    protected static ?string $pluralModelLabel = 'uplate';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('clanarina_id')
                    ->label('Zaduženje')
                    ->relationship('clanarina', 'id')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->getSearchResultsUsing(function (string $search) {
                        return \App\Models\Clanarina::with(['clan', 'period'])
                            ->whereHas('clan', function ($query) use ($search) {
                                $query->where('ime', 'like', "%{$search}%")
                                    ->orWhere('prezime', 'like', "%{$search}%")
                                    ->orWhere('jmbg', 'like', "%{$search}%");
                            })
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn ($c) => [
                                $c->id => $c->clan->ime . ' ' . $c->clan->prezime . ' - ' . $c->period->naziv . ' (Dug: ' . number_format($c->dug, 2) . ' RSD)',
                            ])
                            ->toArray();
                    })
                    ->createOptionForm([
                        Forms\Components\Select::make('clan_id')
                            ->label('Član')
                            ->relationship('clan', 'ime')
                            ->required(),
                        Forms\Components\Select::make('period_id')
                            ->label('Period')
                            ->relationship('period', 'naziv')
                            ->required(),
                        Forms\Components\Select::make('kategorija_id')
                            ->label('Kategorija')
                            ->relationship('kategorija', 'naziv')
                            ->required(),
                        Forms\Components\TextInput::make('iznos_zaduzenja')
                            ->label('Iznos')
                            ->numeric()
                            ->required(),
                    ]),
                Forms\Components\TextInput::make('iznos')
                    ->label('Iznos uplate')
                    ->numeric()
                    ->prefix('RSD')
                    ->required(),
                Forms\Components\DatePicker::make('datum')
                    ->label('Datum uplate')
                    ->default(now())
                    ->required(),
                Forms\Components\Select::make('nacin')
                    ->label('Način plaćanja')
                    ->options([
                        'gotovina' => 'Gotovina',
                        'racun' => 'Tekući račun',
                        'kartica' => 'Platna kartica',
                        'e-banking' => 'E-banking',
                    ])
                    ->default('gotovina')
                    ->required(),
                Forms\Components\TextInput::make('referenca')
                    ->label('Referenca / Poziv na broj')
                    ->maxLength(100),
            ])
            ->columns(2);
    }

    /**
     * Kolone koje se mogu prikazati na listi uplata.
     *
     * Izbor se čuva u podešavanju `uplate_kolone`
     * (Administracija → Konfiguracija sistema).
     *
     * @return array<string, array{label: string, kolona: \Closure}>
     */
    public static function dostupneKolone(): array
    {
        return [
            'clan_clanski_broj' => [
                'label' => 'Br. karte',
                'kolona' => fn () => Tables\Columns\TextColumn::make('clanarina.clan.clanski_broj')
                    ->label('Br. karte')
                    ->searchable(),
            ],
            'clan' => [
                'label' => 'Član',
                'kolona' => fn () => Tables\Columns\TextColumn::make('clanarina.clan.ime')
                    ->label('Član')
                    ->formatStateUsing(fn (Uplata $record): string =>
                        $record->clanarina->clan->ime . ' ' . $record->clanarina->clan->prezime
                    )
                    ->searchable(['clanarina.clan.ime', 'clanarina.clan.prezime']),
            ],
            'period' => [
                'label' => 'Period',
                'kolona' => fn () => Tables\Columns\TextColumn::make('clanarina.period.naziv')
                    ->label('Period')
                    ->sortable(),
            ],
            'iznos' => [
                'label' => 'Iznos',
                'kolona' => fn () => Tables\Columns\TextColumn::make('iznos')
                    ->label('Iznos')
                    ->numeric()
                    ->prefix('RSD')
                    ->sortable(),
            ],
            'datum' => [
                'label' => 'Datum',
                'kolona' => fn () => Tables\Columns\TextColumn::make('datum')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),
            ],
            'nacin' => [
                'label' => 'Način',
                'kolona' => fn () => Tables\Columns\TextColumn::make('nacin')
                    ->label('Način')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'gotovina' => 'Gotovina',
                        'racun' => 'Tekući račun',
                        'kartica' => 'Platna kartica',
                        'e-banking' => 'E-banking',
                        default => $state,
                    })
                    ->badge(),
            ],
            'referenca' => [
                'label' => 'Referenca',
                'kolona' => fn () => Tables\Columns\TextColumn::make('referenca')
                    ->label('Referenca')
                    ->searchable(),
            ],
            'evidentirao' => [
                'label' => 'Evidentirao',
                'kolona' => fn () => Tables\Columns\TextColumn::make('korisnik.name')
                    ->label('Evidentirao')
                    ->sortable(),
            ],
        ];
    }

    /**
     * Kolone koje se prikazuju ako podešavanje nije zadato.
     *
     * @return list<string>
     */
    public static function podrazumevaneKolone(): array
    {
        return ['clan', 'period', 'iznos', 'datum', 'nacin', 'referenca', 'evidentirao'];
    }

    public static function kljucPodesavanjaKolona(): string
    {
        return 'uplate_kolone';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::koloneTabele())
            ->filters([
                Tables\Filters\SelectFilter::make('nacin')
                    ->label('Način plaćanja')
                    ->options([
                        'gotovina' => 'Gotovina',
                        'racun' => 'Tekući račun',
                        'kartica' => 'Platna kartica',
                        'e-banking' => 'E-banking',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUplatas::route('/'),
            'create' => Pages\CreateUplata::route('/create'),
            'edit' => Pages\EditUplata::route('/{record}/edit'),
        ];
    }
}
