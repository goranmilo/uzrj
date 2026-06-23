<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClanarinaKategorijaResource\Pages;
use App\Models\ClanarinaKategorija;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClanarinaKategorijaResource extends Resource
{
    protected static ?string $model = ClanarinaKategorija::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Administracija';
    protected static ?string $navigationLabel = 'Kategorije članarine';
    protected static ?string $modelLabel = 'kategorija članarine';
    protected static ?string $pluralModelLabel = 'kategorije članarine';
    protected static ?int $navigationSort = 13;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('naziv')->required()->maxLength(255),
            Forms\Components\TextInput::make('iznos')
                ->required()
                ->numeric()
                ->prefix('RSD'),
            Forms\Components\Toggle::make('aktivno')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('naziv')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('iznos')->numeric()->prefix('RSD')->sortable(),
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
            'index' => Pages\ListClanarinaKategorijas::route('/'),
            'create' => Pages\CreateClanarinaKategorija::route('/create'),
            'edit' => Pages\EditClanarinaKategorija::route('/{record}/edit'),
        ];
    }
}
