<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EdukacijaResource\Pages;
use App\Models\Edukacija;
use App\Services\EdukacijaService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EdukacijaResource extends Resource
{
    protected static ?string $model = Edukacija::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Edukacije';
    protected static ?string $navigationLabel = 'Edukacije';
    protected static ?string $modelLabel = 'edukacija';
    protected static ?string $pluralModelLabel = 'edukacije';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Osnovni podaci')
                    ->schema([
                        Forms\Components\TextInput::make('naziv')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('opis')
                            ->rows(3),
                        Forms\Components\TextInput::make('lokacija')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('predavaci')
                            ->label('Predavači')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Termin')
                    ->schema([
                        Forms\Components\DateTimePicker::make('datum_pocetka')
                            ->label('Datum i vreme početka')
                            ->required(),
                        Forms\Components\DateTimePicker::make('datum_zavrsetka')
                            ->label('Datum i vreme završetka'),
                        Forms\Components\TextInput::make('kapacitet')
                            ->numeric()
                            ->minValue(1),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('KME / Akreditacija')
                    ->schema([
                        Forms\Components\TextInput::make('akreditacioni_broj')
                            ->label('Broj akreditacije')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('vrsta_kme')
                            ->label('Vrsta KME')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('bodovi')
                            ->numeric()
                            ->step(0.5)
                            ->default(0),
                        Forms\Components\TextInput::make('ciljna_grupa')
                            ->label('Ciljna grupa')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'planirana' => 'Planirana',
                                'odrzana' => 'Održana',
                                'otkazana' => 'Otkazana',
                            ])
                            ->default('planirana')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('naziv')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('datum_pocetka')
                    ->label('Datum')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lokacija')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bodovi')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prijavljeni_count')
                    ->label('Prijavljeno')
                    ->getStateUsing(fn (Edukacija $record): string => 
                        $record->prijavljeni_count . ($record->kapacitet ? '/' . $record->kapacitet : '')
                    ),
                Tables\Columns\TextColumn::make('prisutni_count')
                    ->label('Prisutno')
                    ->getStateUsing(fn (Edukacija $record): string => (string) $record->prisutni_count),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'planirana' => 'info',
                        'odrzana' => 'success',
                        'otkazana' => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'planirana' => 'Planirana',
                        'odrzana' => 'Održana',
                        'otkazana' => 'Otkazana',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('oznaciOdrzano')
                    ->label('Označi kao održano')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Edukacija $record) {
                        EdukacijaService::oznaciKaoOdrzanu($record);
                        
                        Notification::make()
                            ->title('Edukacija označena kao održana')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Edukacija $record): bool => $record->status === 'planirana'),
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
            'index' => Pages\ListEdukacijas::route('/'),
            'create' => Pages\CreateEdukacija::route('/create'),
            'edit' => Pages\EditEdukacija::route('/{record}/edit'),
            'prisustvo' => Pages\PrisustvoEdukacije::route('/{record}/prisustvo'),
            'qr' => Pages\QrEdukacije::route('/{record}/qr'),
        ];
    }
}
