<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClanResource\Pages;
use App\Filament\Resources\ClanResource\RelationManagers;
use App\Models\Clan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

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
                            ->rule(new \App\Rules\Jmbg()),
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
                    ->schema([
                        Forms\Components\TextInput::make('licenca.broj')
                            ->label('Broj licence')
                            ->maxLength(50),
                        Forms\Components\DatePicker::make('licenca.datum_izdavanja')
                            ->label('Datum izdavanja licence'),
                        Forms\Components\DatePicker::make('licenca.datum_isteka')
                            ->label('Datum isteka licence'),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('clanski_broj')
                    ->label('Br. karte')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ime')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prezime')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jmbg')
                    ->label('JMBG')
                    ->searchable(),
                Tables\Columns\TextColumn::make('zvanje.naziv')
                    ->label('Zvanje')
                    ->sortable(),
                Tables\Columns\TextColumn::make('kategorijaClanarine.naziv')
                    ->label('Kategorija')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktivan' => 'success',
                        'neaktivan' => 'gray',
                        'suspendovan' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('datum_uclanjenja')
                    ->label('Učlanjen')
                    ->date('d.m.Y')
                    ->sortable(),
            ])
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
