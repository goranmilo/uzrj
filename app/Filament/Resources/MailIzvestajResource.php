<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MailIzvestajResource\Pages;
use App\Models\MailIzvestaj;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MailIzvestajResource extends Resource
{
    protected static ?string $model = MailIzvestaj::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';
    protected static ?string $navigationGroup = 'Administracija';
    protected static ?string $navigationLabel = 'Log mejlova';
    protected static ?string $modelLabel = 'log mejla';
    protected static ?string $pluralModelLabel = 'logovi mejlova';
    protected static ?int $navigationSort = 18;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informacije')
                    ->schema([
                        Forms\Components\TextInput::make('tip')
                            ->label('Tip')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('poslat_at')
                            ->label('Poslato')
                            ->disabled(),
                        Forms\Components\TextInput::make('status')
                            ->label('Status')
                            ->disabled(),
                        Forms\Components\Textarea::make('greska')
                            ->label('Greška')
                            ->disabled()
                            ->rows(3),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Član')
                    ->schema([
                        Forms\Components\TextInput::make('clan.ime')
                            ->label('Ime')
                            ->disabled(),
                        Forms\Components\TextInput::make('clan.prezime')
                            ->label('Prezime')
                            ->disabled(),
                        Forms\Components\TextInput::make('clan.email')
                            ->label('Email')
                            ->disabled(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('poslat_at')
                    ->label('Poslato')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('clan.ime')
                    ->label('Član')
                    ->formatStateUsing(fn (MailIzvestaj $record): string => 
                        $record->clan->ime . ' ' . $record->clan->prezime
                    )
                    ->searchable(['clan.ime', 'clan.prezime']),
                Tables\Columns\TextColumn::make('clan.email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tip')
                    ->label('Tip')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'mesecni_izvestaj' => 'info',
                        'podsetnik_clanarine' => 'warning',
                        'podsetnik_edukacija' => 'success',
                        'upozorenje_bodovi' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'mesecni_izvestaj' => 'Mesečni izveštaj',
                        'podsetnik_clanarine' => 'Podsetnik članarine',
                        'podsetnik_edukacija' => 'Podsetnik edukacije',
                        'upozorenje_bodovi' => 'Upozorenje bodovi',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'poslat' => 'success',
                        'greska' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tip')
                    ->label('Tip')
                    ->options([
                        'mesecni_izvestaj' => 'Mesečni izveštaj',
                        'podsetnik_clanarine' => 'Podsetnik članarine',
                        'podsetnik_edukacija' => 'Podsetnik edukacije',
                        'upozorenje_bodovi' => 'Upozorenje bodovi',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'poslat' => 'Poslat',
                        'greska' => 'Greška',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
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
            'index' => Pages\ListMailIzvestajs::route('/'),
            'view' => Pages\ViewMailIzvestaj::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()->isAdmin();
    }
}
