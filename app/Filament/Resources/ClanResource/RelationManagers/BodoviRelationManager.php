<?php

namespace App\Filament\Resources\ClanResource\RelationManagers;

use App\Services\BodoviService;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BodoviRelationManager extends RelationManager
{
    protected static string $relationship = 'bodovi';

    protected static ?string $title = 'Bodovi';
    protected static ?string $recordTitleAttribute = 'id';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('edukacija_id')
                    ->label('Edukacija')
                    ->relationship('edukacija', 'naziv')
                    ->nullable(),
                Forms\Components\TextInput::make('bodovi')
                    ->label('Bodovi')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('licencna_godina')
                    ->label('Licencna godina')
                    ->numeric()
                    ->default(fn (): int => BodoviService::licencnaGodina($this->getOwnerRecord()))
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Računa se iz datuma izdavanja licence člana.'),
                Forms\Components\DatePicker::make('datum')
                    ->label('Datum')
                    ->default(now())
                    ->live()
                    ->afterStateUpdated(fn (Forms\Set $set, Forms\Get $get) => $set(
                        'licencna_godina',
                        BodoviService::licencnaGodina($this->getOwnerRecord(), $get('datum') ?: now()),
                    )),
                Forms\Components\Textarea::make('razlog')
                    ->label('Razlog')
                    ->rows(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('edukacija.naziv')
                    ->label('Edukacija')
                    ->searchable()
                    ->sortable()
                    ->default('Ručni unos'),
                Tables\Columns\TextColumn::make('bodovi')
                    ->label('Bodovi')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),
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
            ])
            ->defaultSort('datum', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
