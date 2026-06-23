<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClanarinaPeriodResource\Pages;
use App\Models\ClanarinaPeriod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClanarinaPeriodResource extends Resource
{
    protected static ?string $model = ClanarinaPeriod::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Finansije';
    protected static ?string $navigationLabel = 'Periodi članarine';
    protected static ?string $modelLabel = 'period članarine';
    protected static ?string $pluralModelLabel = 'periodi članarine';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('naziv')
                    ->label('Naziv perioda')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('npr. 2024. godina'),
                Forms\Components\Select::make('vrsta')
                    ->label('Vrsta naplate')
                    ->options([
                        'godisnje' => 'Godišnje',
                        'mesecno' => 'Mesečno',
                        'kvartalno' => 'Kvartalno',
                    ])
                    ->default('godisnje')
                    ->required(),
                Forms\Components\DatePicker::make('vazi_od')
                    ->label('Važi od')
                    ->required(),
                Forms\Components\DatePicker::make('vazi_do')
                    ->label('Važi do')
                    ->required()
                    ->afterOrEqual('vazi_od'),
                Forms\Components\Toggle::make('aktivan')
                    ->label('Aktivan')
                    ->default(true),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('naziv')
                    ->label('Naziv')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vrsta')
                    ->label('Vrsta')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'godisnje' => 'Godišnje',
                        'mesecno' => 'Mesečno',
                        'kvartalno' => 'Kvartalno',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'godisnje' => 'success',
                        'mesecno' => 'info',
                        'kvartalno' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('vazi_od')
                    ->label('Od')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('vazi_do')
                    ->label('Do')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\IconColumn::make('aktivan')
                    ->label('Aktivan')
                    ->boolean(),
                Tables\Columns\TextColumn::make('clanarine_count')
                    ->label('Zaduženja')
                    ->counts('clanarine')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vrsta')
                    ->label('Vrsta')
                    ->options([
                        'godisnje' => 'Godišnje',
                        'mesecno' => 'Mesečno',
                        'kvartalno' => 'Kvartalno',
                    ]),
                Tables\Filters\TernaryFilter::make('aktivan')
                    ->label('Aktivan'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('zaduziSve')
                    ->label('Zaduži sve članove')
                    ->icon('heroicon-o-calculator')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Zaduženje članarine')
                    ->modalDescription('Da li ste sigurni da želite da zadužite SVE aktivne članove za ovaj period?')
                    ->action(function (ClanarinaPeriod $record) {
                        $count = \App\Services\ClanarinaService::zaduziSveClanove($record);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Zaduženje kreirano')
                            ->body("Kreirano je {$count} zaduženja za period: {$record->naziv}")
                            ->success()
                            ->send();
                    }),
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
            'index' => Pages\ListClanarinaPeriods::route('/'),
            'create' => Pages\CreateClanarinaPeriod::route('/create'),
            'edit' => Pages\EditClanarinaPeriod::route('/{record}/edit'),
        ];
    }
}
