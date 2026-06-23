<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Administracija';
    protected static ?string $navigationLabel = 'Audit log';
    protected static ?string $modelLabel = 'zapis';
    protected static ?string $pluralModelLabel = 'audit log';
    protected static ?int $navigationSort = 15;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Osnovni podaci')
                    ->schema([
                        Forms\Components\TextInput::make('akcija')
                            ->label('Akcija')
                            ->required()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('entitet')
                            ->label('Entitet')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('entitet_id')
                            ->label('ID entiteta')
                            ->numeric(),
                        Forms\Components\DateTimePicker::make('vreme')
                            ->label('Vreme')
                            ->required(),
                        Forms\Components\TextInput::make('ip_adresa')
                            ->label('IP adresa')
                            ->maxLength(45),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Korisnik')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Korisnik')
                            ->relationship('korisnik', 'name')
                            ->searchable()
                            ->preload(),
                    ]),

                Forms\Components\Section::make('Promene')
                    ->schema([
                        Forms\Components\KeyValue::make('pre')
                            ->label('Pre')
                            ->disabled(),
                        Forms\Components\KeyValue::make('posle')
                            ->label('Posle')
                            ->disabled(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vreme')
                    ->label('Vreme')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('korisnik.name')
                    ->label('Korisnik')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('akcija')
                    ->label('Akcija')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'create' => 'success',
                        'update' => 'warning',
                        'delete' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('entitet')
                    ->label('Entitet')
                    ->searchable(),
                Tables\Columns\TextColumn::make('entitet_id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip_adresa')
                    ->label('IP adresa'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('akcija')
                    ->label('Akcija')
                    ->options([
                        'create' => 'Kreiranje',
                        'update' => 'Ažuriranje',
                        'delete' => 'Brisanje',
                    ]),
                Tables\Filters\SelectFilter::make('entitet')
                    ->label('Entitet')
                    ->options(fn (): array => 
                        AuditLog::distinct('entitet')
                            ->pluck('entitet', 'entitet')
                            ->toArray()
                    ),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Korisnik')
                    ->relationship('korisnik', 'name')
                    ->searchable()
                    ->preload(),
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
            'index' => Pages\ListAuditLogs::route('/'),
            'view' => Pages\ViewAuditLog::route('/{record}'),
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
