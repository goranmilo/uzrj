<?php

namespace App\Filament\Resources\EdukacijaResource\Pages;

use App\Filament\Resources\EdukacijaResource;
use App\Models\Edukacija;
use App\Models\Clan;
use App\Services\EdukacijaService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PrisustvoEdukacije extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static string $resource = EdukacijaResource::class;
    protected static string $view = 'filament.pages.prisustvo-edukacije';

    public ?Edukacija $record = null;
    public ?int $edukacijaId = null;

    public function mount(int $record): void
    {
        $this->record = Edukacija::findOrFail($record);
        $this->edukacijaId = $record;
    }

    public function getTitle(): string
    {
        return 'Prisustvo: ' . ($this->record?->naziv ?? '');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                $this->record->prisustva()->with('clan')
            )
            ->columns([
                Tables\Columns\TextColumn::make('clan.clanski_broj')
                    ->label('Br. karte')
                    ->searchable(),
                Tables\Columns\TextColumn::make('clan.ime')
                    ->label('Ime')
                    ->searchable(),
                Tables\Columns\TextColumn::make('clan.prezime')
                    ->label('Prezime')
                    ->searchable(),
                Tables\Columns\TextColumn::make('clan.jmbg')
                    ->label('JMBG')
                    ->searchable(),
                Tables\Columns\IconColumn::make('prijavljen')
                    ->label('Prijavljen')
                    ->boolean(),
                Tables\Columns\IconColumn::make('prisutan')
                    ->label('Prisutan')
                    ->boolean(),
                Tables\Columns\TextColumn::make('vreme_cekiranja')
                    ->label('Vreme čekiranja')
                    ->dateTime('d.m.Y H:i'),
                Tables\Columns\TextColumn::make('dodeljeni_bodovi')
                    ->label('Bodovi')
                    ->numeric(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('prijavljen')
                    ->label('Prijavljen'),
                Tables\Filters\TernaryFilter::make('prisutan')
                    ->label('Prisutan'),
            ])
            ->actions([
                Tables\Actions\Action::make('cekiraj')
                    ->label('Čekiraj')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function ($record) {
                        $result = EdukacijaService::rucnoCekiraj(
                            $this->edukacijaId,
                            $record->clan_id
                        );

                        Notification::make()
                            ->title($result['success'] ? 'Uspešno' : 'Greška')
                            ->body($result['message'])
                            ->color($result['success'] ? 'success' : 'danger')
                            ->send();
                    })
                    ->visible(fn ($record): bool => !$record->prisutan),
                Tables\Actions\Action::make('otkaziPrisustvo')
                    ->label('Otkaži')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'prisutan' => false,
                            'vreme_cekiranja' => null,
                        ]);

                        Notification::make()
                            ->title('Prisustvo otkazano')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record): bool => $record->prisutan),
            ])
            ->headerActions([
                Tables\Actions\Action::make('dodajClana')
                    ->label('Dodaj člana')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Forms\Components\Select::make('clan_id')
                            ->label('Član')
                            ->options(Clan::where('status', 'aktivan')->pluck('prezime', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        EdukacijaService::prijaviClana($this->record, $data['clan_id']);

                        Notification::make()
                            ->title('Član prijavljen')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function getRecord(): ?Edukacija
    {
        return $this->record;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\EdukacijaStatsWidget::class,
        ];
    }
}
