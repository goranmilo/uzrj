<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpremaResource\Pages;
use App\Models\Sprema;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SpremaResource extends Resource
{
    protected static ?string $model = Sprema::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Administracija';
    protected static ?string $navigationLabel = 'Stručne spreme';
    protected static ?string $modelLabel = 'stručna sprema';
    protected static ?string $pluralModelLabel = 'stručne spreme';
    protected static ?int $navigationSort = 10;

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
            'index' => Pages\ListSpremas::route('/'),
            'create' => Pages\CreateSprema::route('/create'),
            'edit' => Pages\EditSprema::route('/{record}/edit'),
        ];
    }
}
