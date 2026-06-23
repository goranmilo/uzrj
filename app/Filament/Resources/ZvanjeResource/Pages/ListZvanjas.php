<?php

namespace App\Filament\Resources\ZvanjeResource\Pages;

use App\Filament\Resources\ZvanjeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListZvanjas extends ListRecords
{
    protected static string $resource = ZvanjeResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
