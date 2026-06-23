<?php

namespace App\Filament\Resources\EdukacijaResource\Pages;

use App\Filament\Resources\EdukacijaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEdukacijas extends ListRecords
{
    protected static string $resource = EdukacijaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
