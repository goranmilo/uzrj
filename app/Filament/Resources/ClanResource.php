<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClanResource\Pages;
use App\Filament\Resources\ClanResource\RelationManagers;
use App\Models\Clan;
use App\Models\Podesavanje;
use App\Rules\Jmbg;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClanResource extends Resource
{
    protected static ?string $model = Clan::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Članstvo';

    protected static ?string $navigationLabel = 'Članovi';

    protected static ?string $modelLabel = 'član';

    protected static ?string $pluralModelLabel = 'članovi';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Lični podaci')
                    ->schema([
                        Forms\Components\TextInput::make('ime')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('prezime')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('jmbg')
                            ->label('JMBG')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->length(13)
                            ->numeric()
                            ->rule(new Jmbg),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('telefon')
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Profesionalni podaci')
                    ->schema([
                        Forms\Components\TextInput::make('okg')
                            ->label('Broj komore (OKG)')
                            ->maxLength(50),
                        Forms\Components\Select::make('sprema_id')
                            ->label('Stručna sprema')
                            ->relationship('sprem', 'naziv')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('zvanje_id')
                            ->label('Zvanje')
                            ->relationship('zvanje', 'naziv')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('odeljenje_id')
                            ->label('Odeljenje')
                            ->relationship('odeljenje', 'naziv')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Članstvo')
                    ->schema([
                        Forms\Components\Select::make('kategorija_clanarine_id')
                            ->label('Kategorija članarine')
                            ->relationship('kategorijaClanarine', 'naziv')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'aktivan' => 'Aktivan',
                                'neaktivan' => 'Neaktivan',
                                'suspendovan' => 'Suspendovan',
                            ])
                            ->default('aktivan')
                            ->required(),
                        Forms\Components\DatePicker::make('datum_uclanjenja')
                            ->label('Datum učlanjenja')
                            ->default(now()),
                        Forms\Components\TextInput::make('clanski_broj')
                            ->label('Članski broj')
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Licenca')
                    ->description('Datum izdavanja licence određuje licencnu godinu po kojoj se sabiraju bodovi.')
                    ->schema([
                        Forms\Components\TextInput::make('licenca.broj')
                            ->label('Broj licence')
                            ->maxLength(50)
                            ->live(onBlur: true)
                            ->requiredWith('licenca.datum_izdavanja'),
                        Forms\Components\DatePicker::make('licenca.datum_izdavanja')
                            ->label('Datum izdavanja licence')
                            ->live()
                            ->requiredWith('licenca.broj'),
                        Forms\Components\DatePicker::make('licenca.datum_isteka')
                            ->label('Datum isteka licence')
                            ->afterOrEqual('licenca.datum_izdavanja')
                            ->helperText('Ako se ne unese, računa se kao datum izdavanja + licencni period.'),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }

    /**
     * Kolone koje ostaju vidljive na uskom (mobilnom) ekranu — dovoljno da se
     * prepozna član, bez horizontalnog skrolovanja. Ostale kolone se pojavljuju
     * tek od `lg` širine (~1024px), gde ima mesta za ceo izabrani skup.
     *
     * @var list<string>
     */
    protected const KOLONE_UVEK_VIDLJIVE = ['ime', 'prezime', 'jmbg'];

    /**
     * Kolone koje se mogu prikazati na listi članova.
     *
     * Izbor se čuva u podešavanju `clanovi_kolone`
     * (Administracija → Konfiguracija sistema).
     *
     * @return array<string, array{label: string, kolona: \Closure}>
     */
    public static function dostupneKolone(): array
    {
        $vidljivost = fn (Tables\Columns\TextColumn $kolona, string $kljuc): Tables\Columns\TextColumn => in_array($kljuc, static::KOLONE_UVEK_VIDLJIVE, true)
            ? $kolona
            : $kolona->visibleFrom('lg');

        return [
            'clanski_broj' => [
                'label' => 'Br. karte',
                'kolona' => fn () => $vidljivost(
                    Tables\Columns\TextColumn::make('clanski_broj')
                        ->label('Br. karte')
                        ->searchable()
                        ->sortable(),
                    'clanski_broj',
                ),
            ],
            'ime' => [
                'label' => 'Ime',
                'kolona' => fn () => Tables\Columns\TextColumn::make('ime')
                    ->label('Ime')
                    ->searchable()
                    ->sortable(),
            ],
            'prezime' => [
                'label' => 'Prezime',
                'kolona' => fn () => Tables\Columns\TextColumn::make('prezime')
                    ->label('Prezime')
                    ->searchable()
                    ->sortable(),
            ],
            'jmbg' => [
                'label' => 'JMBG',
                'kolona' => fn () => Tables\Columns\TextColumn::make('jmbg')
                    ->label('JMBG')
                    ->searchable(),
            ],
            'okg' => [
                'label' => 'Broj komore (OKG)',
                'kolona' => fn () => $vidljivost(
                    Tables\Columns\TextColumn::make('okg')
                        ->label('Broj komore')
                        ->searchable(),
                    'okg',
                ),
            ],
            'email' => [
                'label' => 'E-mail',
                'kolona' => fn () => $vidljivost(
                    Tables\Columns\TextColumn::make('email')
                        ->label('E-mail')
                        ->searchable()
                        ->copyable(),
                    'email',
                ),
            ],
            'telefon' => [
                'label' => 'Telefon',
                'kolona' => fn () => $vidljivost(
                    Tables\Columns\TextColumn::make('telefon')
                        ->label('Telefon')
                        ->searchable(),
                    'telefon',
                ),
            ],
            'sprema' => [
                'label' => 'Stručna sprema',
                'kolona' => fn () => $vidljivost(
                    Tables\Columns\TextColumn::make('sprem.naziv')
                        ->label('Sprema')
                        ->sortable(),
                    'sprema',
                ),
            ],
            'zvanje' => [
                'label' => 'Zvanje',
                'kolona' => fn () => $vidljivost(
                    Tables\Columns\TextColumn::make('zvanje.naziv')
                        ->label('Zvanje')
                        ->sortable(),
                    'zvanje',
                ),
            ],
            'odeljenje' => [
                'label' => 'Odeljenje',
                'kolona' => fn () => $vidljivost(
                    Tables\Columns\TextColumn::make('odeljenje.naziv')
                        ->label('Odeljenje')
                        ->sortable(),
                    'odeljenje',
                ),
            ],
            'kategorija_clanarine' => [
                'label' => 'Kategorija članarine',
                'kolona' => fn () => $vidljivost(
                    Tables\Columns\TextColumn::make('kategorijaClanarine.naziv')
                        ->label('Kategorija')
                        ->sortable(),
                    'kategorija_clanarine',
                ),
            ],
            'status' => [
                'label' => 'Status',
                'kolona' => fn () => $vidljivost(
                    Tables\Columns\TextColumn::make('status')
                        ->label('Status')
                        ->badge()
                        ->color(fn (?string $state): string => match ($state) {
                            'aktivan' => 'success',
                            'suspendovan' => 'danger',
                            default => 'gray',
                        }),
                    'status',
                ),
            ],
            'datum_uclanjenja' => [
                'label' => 'Datum učlanjenja',
                'kolona' => fn () => $vidljivost(
                    Tables\Columns\TextColumn::make('datum_uclanjenja')
                        ->label('Učlanjen')
                        ->date('d.m.Y')
                        ->sortable(),
                    'datum_uclanjenja',
                ),
            ],
            'licenca_broj' => [
                'label' => 'Broj licence',
                'kolona' => fn () => $vidljivost(
                    Tables\Columns\TextColumn::make('licenca_broj')
                        ->label('Br. licence')
                        ->state(fn (Clan $record): ?string => $record->merodavna_licenca?->broj),
                    'licenca_broj',
                ),
            ],
            'licenca_datum_isteka' => [
                'label' => 'Istek licence',
                'kolona' => fn () => $vidljivost(
                    Tables\Columns\TextColumn::make('licenca_datum_isteka')
                        ->label('Licenca ističe')
                        ->state(fn (Clan $record): ?string => $record->merodavna_licenca?->datum_isteka?->format('d.m.Y')),
                    'licenca_datum_isteka',
                ),
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
        return [
            'clanski_broj', 'ime', 'prezime', 'jmbg',
            'zvanje', 'kategorija_clanarine', 'status', 'datum_uclanjenja',
        ];
    }

    /**
     * Izabrane kolone iz podešavanja, u redosledu iz kataloga.
     *
     * @return list<string>
     */
    public static function izabraneKolone(): array
    {
        $izabrane = Podesavanje::get('clanovi_kolone');

        if (! is_array($izabrane) || $izabrane === []) {
            $izabrane = static::podrazumevaneKolone();
        }

        $poredak = array_keys(static::dostupneKolone());

        return array_values(array_intersect($poredak, $izabrane));
    }

    public static function getEloquentQuery(): Builder
    {
        // Kolone sa podacima iz veza (uključujući licencu) se učitavaju unapred.
        return parent::getEloquentQuery()->with([
            'sprem', 'zvanje', 'odeljenje', 'kategorijaClanarine', 'licence',
        ]);
    }

    public static function table(Table $table): Table
    {
        $katalog = static::dostupneKolone();

        return $table
            ->columns(
                collect(static::izabraneKolone())
                    ->map(fn (string $kljuc) => ($katalog[$kljuc]['kolona'])())
                    ->all()
            )
            ->filters([
                Tables\Filters\SelectFilter::make('odeljenje_id')
                    ->label('Odeljenje')
                    ->relationship('odeljenje', 'naziv')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('zvanje_id')
                    ->label('Zvanje')
                    ->relationship('zvanje', 'naziv')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('sprema_id')
                    ->label('Stručna sprema')
                    ->relationship('sprem', 'naziv')
                    ->searchable()
                    ->preload(),
            ])
            // Brisanje člana je namerno dostupno samo na stranici za izmenu.
            // Grupisano u jedno dugme (⋮) — dva odvojena dugmeta sa natpisom
            // (View/Edit) se nisu uklapala pored Ime/Prezime/JMBG na uskom ekranu.
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                ]),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PrisustvaRelationManager::class,
            RelationManagers\BodoviRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClans::route('/'),
            'create' => Pages\CreateClan::route('/create'),
            'edit' => Pages\EditClan::route('/{record}/edit'),
            'view' => Pages\ViewClan::route('/{record}'),
        ];
    }
}
