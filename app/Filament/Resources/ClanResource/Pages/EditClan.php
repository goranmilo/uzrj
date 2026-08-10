<?php

namespace App\Filament\Resources\ClanResource\Pages;

use App\Filament\Resources\ClanResource;
use App\Services\LicencaService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClan extends EditRecord
{
    protected static string $resource = ClanResource::class;

    /**
     * Podaci o licenci iz forme — ne pripadaju modelu Clan.
     *
     * @var array<string, mixed>
     */
    protected array $licencaPodaci = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['licenca'] = LicencaService::podaciZaFormu($this->getRecord());

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->licencaPodaci = $data['licenca'] ?? [];
        unset($data['licenca']);

        return $data;
    }

    protected function afterSave(): void
    {
        LicencaService::sacuvaj($this->getRecord(), $this->licencaPodaci);
    }
}
