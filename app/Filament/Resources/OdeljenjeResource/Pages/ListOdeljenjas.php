<?php

namespace App\Filament\Resources\OdeljenjeResource\Pages;

use App\Filament\Resources\OdeljenjeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOdeljenjas extends ListRecords
{
    protected static string $resource = OdeljenjeResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
