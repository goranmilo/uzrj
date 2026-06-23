<?php

namespace App\Filament\Resources\ClanarinaPeriodResource\Pages;

use App\Filament\Resources\ClanarinaPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClanarinaPeriods extends ListRecords
{
    protected static string $resource = ClanarinaPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
