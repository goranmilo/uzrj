<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UplataResource\Pages;
use App\Models\Uplata;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UplataResource extends Resource
{
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('clanarina.clan.ime')
                    ->label('Član')
                    ->formatStateUsing(fn (Uplata $record): string => 
                        $record->clanarina->clan->ime . ' ' . $record->clanarina->clan->prezime
                    )
                    ->searchable(['clanarina.clan.ime', 'clanarina.clan.prezime']),
                Tables\Columns\TextColumn::make('clanarina.period.naziv')
                    ->label('Period')
                    ->sortable(),
                Tables\Columns\TextColumn::make('iznos')
                    ->label('Iznos')
                    ->numeric()
                    ->prefix('RSD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('datum')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nacin')
                    ->label('Način')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'gotovina' => 'Gotovina',
                        'racun' => 'Tekući račun',
                        'kartica' => 'Platna kartica',
                        'e-banking' => 'E-banking',
                        default => $state,
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('referenca')
                    ->label('Referenca')
                    ->searchable(),
                Tables\Columns\TextColumn::make('korisnik.name')
                    ->label('Evidentirao')
                    ->sortable(),
            ])
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
