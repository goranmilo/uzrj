<?php

namespace App\Filament\Resources\EdukacijaResource\Pages;

use App\Filament\Resources\EdukacijaResource;
use App\Models\Edukacija;
use App\Services\EdukacijaService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class QrEdukacije extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = EdukacijaResource::class;
    protected static string $view = 'filament.pages.qr-edukacije';

    public ?Edukacija $record = null;
    public ?array $qrCodes = [];
    public ?string $selectedQr = null;

    public function mount(int $record): void
    {
        $this->record = Edukacija::findOrFail($record);
        $this->loadQrCodes();
    }

    public function getTitle(): string
    {
        return 'QR kodovi: ' . ($this->record?->naziv ?? '');
    }

    protected function loadQrCodes(): void
    {
        $this->qrCodes = $this->record->prisustva()
            ->where('prijavljen', true)
            ->with('clan')
            ->get()
            ->map(fn ($prisustvo) => [
                'id' => $prisustvo->id,
                'clan' => $prisustvo->clan->ime . ' ' . $prisustvo->clan->prezime,
                'clanski_broj' => $prisustvo->clan->clanski_broj,
                'qr_token' => $prisustvo->qr_token,
                'qr_svg' => EdukacijaService::generisiQrKod($prisustvo->qr_token),
                'prisutan' => $prisustvo->prisutan,
            ])
            ->toArray();
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('selectedQr')
                ->label('Izaberite člana')
                ->options(collect($this->qrCodes)->pluck('clan', 'id')->toArray())
                ->reactive()
                ->afterStateUpdated(function ($state) {
                    $this->selectedQr = $state;
                }),
        ];
    }

    public function getSelectedQrData(): ?array
    {
        if (!$this->selectedQr) {
            return null;
        }

        return collect($this->qrCodes)->firstWhere('id', (int) $this->selectedQr);
    }

    public function getQrSvg(): ?string
    {
        $data = $this->getSelectedQrData();
        return $data['qr_svg'] ?? null;
    }
}
