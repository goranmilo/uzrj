<?php

namespace App\Filament\Resources\ClanarinaResource\Pages;

use App\Filament\Resources\ClanarinaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClanarinas extends ListRecords
{
    protected static string $resource = ClanarinaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
