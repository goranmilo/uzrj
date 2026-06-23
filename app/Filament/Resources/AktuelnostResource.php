<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AktuelnostResource\Pages;
use App\Models\Aktuelnost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AktuelnostResource extends Resource
{
    protected static ?string $model = Aktuelnost::class;
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationGroup = 'Administracija';
    protected static ?string $navigationLabel = 'Aktuelnosti';
    protected static ?string $modelLabel = 'vest';
    protected static ?string $pluralModelLabel = 'aktuelnosti';
    protected static ?int $navigationSort = 16;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('naslov')
                    ->label('Naslov')
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make('sadrzaj')
                    ->label('Sadržaj')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('datum_objave')
                    ->label('Datum objave')
                    ->default(now())
                    ->required(),
                Forms\Components\Toggle::make('objavljeno')
                    ->label('Objavljeno')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('naslov')
                    ->label('Naslov')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('datum_objave')
                    ->label('Datum')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('objavljeno')
                    ->label('Objavljeno')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Kreirano')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('objavljeno')
                    ->label('Objavljeno'),
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
            'index' => Pages\ListAktuelnosts::route('/'),
            'create' => Pages\CreateAktuelnost::route('/create'),
            'edit' => Pages\EditAktuelnost::route('/{record}/edit'),
        ];
    }
}
