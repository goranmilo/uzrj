<?php

namespace App\Filament\Pages;

use App\Models\Edukacija;
use App\Services\EdukacijaService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class QrScanner extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationGroup = 'Edukacije';
    protected static ?string $navigationLabel = 'QR skener';
    protected static ?string $title = 'Skeniranje QR kodova';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.qr-scanner';

    public ?int $selectedEdukacijaId = null;
    public ?string $qrCode = null;
    public ?array $lastResult = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('selectedEdukacijaId')
                ->label('Edukacija')
                ->options(
                    Edukacija::where('status', 'planirana')
                        ->orWhere('status', 'odrzana')
                        ->orderBy('datum_pocetka', 'desc')
                        ->pluck('naziv', 'id')
                )
                ->searchable()
                ->required()
                ->reactive(),
            Forms\Components\TextInput::make('qrCode')
                ->label('QR kod')
                ->placeholder('Unesite ili skenirajte QR kod')
                ->required()
                ->maxLength(36),
        ];
    }

    public function scan(): void
    {
        $data = $this->form->getState();

        if (empty($data['selectedEdukacijaId']) || empty($data['qrCode'])) {
            Notification::make()
                ->title('Greška')
                ->body('Izaberite edukaciju i unesite QR kod.')
                ->danger()
                ->send();
            return;
        }

        $result = EdukacijaService::cekirajPrisustvo(
            $data['qrCode'],
            $data['selectedEdukacijaId']
        );

        $this->lastResult = $result;

        Notification::make()
            ->title($result['success'] ? 'Uspešno!' : 'Greška')
            ->body($result['message'] . (isset($result['clan']) ? ' - ' . $result['clan'] : ''))
            ->color($result['success'] ? 'success' : 'danger')
            ->send();

        // Reset QR code field
        $this->form->fill([
            'selectedEdukacijaId' => $data['selectedEdukacijaId'],
            'qrCode' => '',
        ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->hasRole(['admin', 'operater']) ?? false;
    }
}
