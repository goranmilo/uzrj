<?php

namespace App\Filament\Resources\ClanarinaKategorijaResource\Pages;

use App\Filament\Resources\ClanarinaKategorijaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClanarinaKategorijas extends ListRecords
{
    protected static string $resource = ClanarinaKategorijaResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
