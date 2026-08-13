<?php

namespace App\Filament\Resources\ClanResource\Pages;

use App\Filament\Resources\ClanResource;
use App\Filament\Widgets\ClanBodoviWidget;
use App\Services\LicencaService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewClan extends ViewRecord
{
    protected static string $resource = ClanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Licenca se čuva u zasebnoj tabeli, pa je forma ne popunjava sama.
        $data['licenca'] = LicencaService::podaciZaFormu($this->getRecord());

        return $data;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ClanBodoviWidget::class,
        ];
    }
}
