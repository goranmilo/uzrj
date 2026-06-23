<?php

namespace App\Filament\Resources\ClanarinaPeriodResource\Pages;

use App\Filament\Resources\ClanarinaPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClanarinaPeriod extends EditRecord
{
    protected static string $resource = ClanarinaPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
