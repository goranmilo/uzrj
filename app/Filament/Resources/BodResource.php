<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BodResource\Pages;
use App\Models\Bod;
use App\Services\BodoviService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BodResource extends Resource
{
    protected static ?string $model = Bod::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Edukacije';
    protected static ?string $navigationLabel = 'Bodovi';
    protected static ?string $modelLabel = 'bod';
    protected static ?string $pluralModelLabel = 'bodovi';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('clan_id')
                    ->label('Član')
                    ->relationship('clan', 'ime')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => $set(
                        'licencna_godina',
                        BodoviService::licencnaGodinaZaClan($get('clan_id'), $get('datum')),
                    )),
                Forms\Components\Select::make('edukacija_id')
                    ->label('Edukacija')
                    ->relationship('edukacija', 'naziv')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\TextInput::make('bodovi')
                    ->label('Bodovi')
                    ->numeric()
                    ->step(0.5)
                    ->required(),
                Forms\Components\TextInput::make('licencna_godina')
                    ->label('Licencna godina')
                    ->numeric()
                    ->default(now()->year)
                    ->required()
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Kalendarska godina u kojoj počinje licencna godina člana — računa se iz datuma izdavanja licence.'),
                Forms\Components\DatePicker::make('datum')
                    ->label('Datum')
                    ->default(now())
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => $set(
                        'licencna_godina',
                        BodoviService::licencnaGodinaZaClan($get('clan_id'), $get('datum')),
                    )),
                Forms\Components\Textarea::make('razlog')
                    ->label('Razlog / Napomena')
                    ->rows(2),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('clan.ime')
                    ->label('Član')
                    ->formatStateUsing(fn (Bod $record): string => 
                        $record->clan->ime . ' ' . $record->clan->prezime
                    )
                    ->searchable(['clan.ime', 'clan.prezime'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('edukacija.naziv')
                    ->label('Edukacija')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bodovi')
                    ->label('Bodovi')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('licencna_godina')
                    ->label('Lic. godina')
                    ->sortable(),
                Tables\Columns\TextColumn::make('datum')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('razlog')
                    ->label('Razlog')
                    ->limit(50),
                Tables\Columns\TextColumn::make('korisnik.name')
                    ->label('Evidentirao')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('licencna_godina')
                    ->label('Licencna godina')
                    ->options(fn (): array => 
                        Bod::distinct('licencna_godina')
                            ->pluck('licencna_godina', 'licencna_godina')
                            ->toArray()
                    ),
                Tables\Filters\SelectFilter::make('clan_id')
                    ->label('Član')
                    ->relationship('clan', 'ime')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBods::route('/'),
            'create' => Pages\CreateBod::route('/create'),
            'edit' => Pages\EditBod::route('/{record}/edit'),
        ];
    }
}
