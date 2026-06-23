<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ZvanjeResource\Pages;
use App\Models\Zvanje;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ZvanjeResource extends Resource
{
    protected static ?string $model = Zvanje::class;
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Administracija';
    protected static ?string $navigationLabel = 'Zvanja';
    protected static ?string $modelLabel = 'zvanje';
    protected static ?string $pluralModelLabel = 'zvanja';
    protected static ?int $navigationSort = 11;

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
            'index' => Pages\ListZvanjas::route('/'),
            'create' => Pages\CreateZvanje::route('/create'),
            'edit' => Pages\EditZvanje::route('/{record}/edit'),
        ];
    }
}
