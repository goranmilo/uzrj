<?php

namespace App\Filament\Resources\BodResource\Pages;

use App\Filament\Resources\BodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBods extends ListRecords
{
    protected static string $resource = BodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
