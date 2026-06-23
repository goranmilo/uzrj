<?php

namespace App\Filament\Pages;

use App\Models\Clan;
use App\Services\BodoviService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class BodoviPregled extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Edukacije';
    protected static ?string $navigationLabel = 'Pregled bodova';
    protected static ?string $title = 'Pregled bodova članova';
    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.bodovi-pregled';

    public ?int $selectedClanId = null;
    public ?array $clanStatus = null;
    public ?array $statistike = null;

    public function mount(): void
    {
        $this->statistike = BodoviService::statistike();
        $this->form->fill();
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('selectedClanId')
                ->label('Izaberite člana')
                ->options(
                    Clan::where('status', 'aktivan')
                        ->orderBy('prezime')
                        ->orderBy('ime')
                        ->get()
                        ->mapWithKeys(fn ($clan) => [
                            $clan->id => $clan->prezime . ' ' . $clan->ime . ' (' . $clan->jmbg . ')'
                        ])
                        ->toArray()
                )
                ->searchable()
                ->reactive()
                ->afterStateUpdated(function ($state) {
                    $this->selectedClanId = $state;
                    $this->ucitajStatus();
                }),
        ];
    }

    public function ucitajStatus(): void
    {
        if (!$this->selectedClanId) {
            $this->clanStatus = null;
            return;
        }

        $clan = Clan::with('licence')->findOrFail($this->selectedClanId);
        $this->clanStatus = BodoviService::statusNapretka($clan);
        $this->clanStatus['clan'] = $clan->ime . ' ' . $clan->prezime;
        $this->clanStatus['jmbg'] = $clan->jmbg;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->hasRole(['admin', 'operater']) ?? false;
    }
}
