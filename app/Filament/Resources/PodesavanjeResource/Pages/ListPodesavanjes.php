<?php

namespace App\Filament\Resources\PodesavanjeResource\Pages;

use App\Filament\Resources\PodesavanjeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPodesavanjes extends ListRecords
{
    protected static string $resource = PodesavanjeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
