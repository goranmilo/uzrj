<?php

namespace App\Filament\Resources\ClanResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PrisustvaRelationManager extends RelationManager
{
    protected static string $relationship = 'prisustva';

    protected static ?string $title = 'Edukacije';
    protected static ?string $recordTitleAttribute = 'id';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('edukacija_id')
                    ->label('Edukacija')
                    ->relationship('edukacija', 'naziv')
                    ->required(),
                Forms\Components\Toggle::make('prijavljen')
                    ->label('Prijavljen')
                    ->default(true),
                Forms\Components\Toggle::make('prisutan')
                    ->label('Prisutan'),
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('edukacija.datum_pocetka')
                    ->label('Datum')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('edukacija.bodovi')
                    ->label('Bodovi')
                    ->numeric(),
                Tables\Columns\IconColumn::make('prijavljen')
                    ->label('Prijavljen')
                    ->boolean(),
                Tables\Columns\IconColumn::make('prisutan')
                    ->label('Prisutan')
                    ->boolean(),
                Tables\Columns\TextColumn::make('vreme_cekiranja')
                    ->label('Čekiran')
                    ->dateTime('d.m.Y H:i'),
                Tables\Columns\TextColumn::make('dodeljeni_bodovi')
                    ->label('Dobijeni bodovi')
                    ->numeric()
                    ->badge()
                    ->color('success'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('prisutan')
                    ->label('Prisutan'),
            ])
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
