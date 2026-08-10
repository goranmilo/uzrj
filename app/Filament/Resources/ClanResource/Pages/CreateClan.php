<?php

namespace App\Filament\Resources\ClanResource\Pages;

use App\Filament\Resources\ClanResource;
use App\Services\LicencaService;
use Filament\Resources\Pages\CreateRecord;

class CreateClan extends CreateRecord
{
    protected static string $resource = ClanResource::class;

    /**
     * Podaci o licenci iz forme — ne pripadaju modelu Clan.
     *
     * @var array<string, mixed>
     */
    protected array $licencaPodaci = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->licencaPodaci = $data['licenca'] ?? [];
        unset($data['licenca']);

        return $data;
    }

    protected function afterCreate(): void
    {
        LicencaService::sacuvaj($this->record, $this->licencaPodaci);
    }
}
