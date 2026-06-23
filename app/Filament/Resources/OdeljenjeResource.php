<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OdeljenjeResource\Pages;
use App\Models\Odeljenje;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OdeljenjeResource extends Resource
{
    protected static ?string $model = Odeljenje::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Administracija';
    protected static ?string $navigationLabel = 'Odeljenja';
    protected static ?string $modelLabel = 'odeljenje';
    protected static ?string $pluralModelLabel = 'odeljenja';
    protected static ?int $navigationSort = 12;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('naziv')->required()->maxLength(255),
            Forms\Components\Toggle::make('aktivno')->default(true),
            Forms\Components\TextInput::make('redosled')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('naziv')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('redosled')->sortable(),
                Tables\Columns\IconColumn::make('aktivno')->boolean(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOdeljenjas::route('/'),
            'create' => Pages\CreateOdeljenje::route('/create'),
            'edit' => Pages\EditOdeljenje::route('/{record}/edit'),
        ];
    }
}
