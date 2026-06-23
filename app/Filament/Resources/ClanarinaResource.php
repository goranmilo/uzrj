<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClanarinaResource\Pages;
use App\Models\Clanarina;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClanarinaResource extends Resource
{
    protected static ?string $model = Clanarina::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finansije';
    protected static ?string $navigationLabel = 'Članarine';
    protected static ?string $modelLabel = 'članarina';
    protected static ?string $pluralModelLabel = 'članarine';
    protected static ?int $navigationSort = 1;

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
                    ->createOptionForm([
                        Forms\Components\TextInput::make('ime')->required(),
                        Forms\Components\TextInput::make('prezime')->required(),
                        Forms\Components\TextInput::make('jmbg')->required()->length(13),
                    ]),
                Forms\Components\Select::make('period_id')
                    ->label('Period')
                    ->relationship('period', 'naziv')
                    ->required(),
                Forms\Components\Select::make('kategorija_id')
                    ->label('Kategorija')
                    ->relationship('kategorija', 'naziv')
                    ->required(),
                Forms\Components\TextInput::make('iznos_zaduzenja')
                    ->label('Iznos zaduženja')
                    ->numeric()
                    ->prefix('RSD')
                    ->required(),
                Forms\Components\TextInput::make('iznos_placen')
                    ->label('Iznos plaćeno')
                    ->numeric()
                    ->prefix('RSD')
                    ->default(0),
                Forms\Components\Select::make('status')
                    ->options([
                        'placeno' => 'Plaćeno',
                        'delimicno' => 'Delimično',
                        'dug' => 'Dug',
                    ])
                    ->default('dug')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('clan.ime')
                    ->label('Član')
                    ->formatStateUsing(fn (Clanarina $record): string => $record->clan->ime . ' ' . $record->clan->prezime)
                    ->searchable(['clan.ime', 'clan.prezime'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('period.naziv')
                    ->label('Period')
                    ->sortable(),
                Tables\Columns\TextColumn::make('kategorija.naziv')
                    ->label('Kategorija')
                    ->sortable(),
                Tables\Columns\TextColumn::make('iznos_zaduzenja')
                    ->label('Zaduženo')
                    ->numeric()
                    ->prefix('RSD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('iznos_placen')
                    ->label('Plaćeno')
                    ->numeric()
                    ->prefix('RSD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('dug')
                    ->label('Dug')
                    ->getStateUsing(fn (Clanarina $record): float => $record->dug)
                    ->numeric()
                    ->prefix('RSD'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'placeno' => 'success',
                        'delimicno' => 'warning',
                        'dug' => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'placeno' => 'Plaćeno',
                        'delimicno' => 'Delimično',
                        'dug' => 'Dug',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('uplati')
                    ->label('Evidentiraj uplatu')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('iznos')
                            ->label('Iznos uplate')
                            ->numeric()
                            ->prefix('RSD')
                            ->required(),
                        Forms\Components\DatePicker::make('datum')
                            ->label('Datum')
                            ->default(now())
                            ->required(),
                        Forms\Components\Select::make('nacin')
                            ->label('Način plaćanja')
                            ->options([
                                'gotovina' => 'Gotovina',
                                'racun' => 'Tekući račun',
                                'kartica' => 'Platna kartica',
                                'e-banking' => 'E-banking',
                            ])
                            ->default('gotovina')
                            ->required(),
                        Forms\Components\TextInput::make('referenca')
                            ->label('Referenca')
                            ->maxLength(100),
                    ])
                    ->action(function (Clanarina $record, array $data): void {
                        \App\Services\ClanarinaService::evidentirajUplatu(
                            $record,
                            $data['iznos'],
                            $data['nacin'],
                            $data['referenca'] ?? null
                        );

                        \Filament\Notifications\Notification::make()
                            ->title('Uplata evidentirana')
                            ->body("Uplata od " . number_format($data['iznos'], 2) . " RSD je uspešno evidentirana.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Clanarina $record): bool => $record->status !== 'placeno'),
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
            'index' => Pages\ListClanarinas::route('/'),
            'create' => Pages\CreateClanarina::route('/create'),
            'edit' => Pages\EditClanarina::route('/{record}/edit'),
        ];
    }
}
