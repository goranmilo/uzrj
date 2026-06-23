<?php

namespace App\Filament\Resources\ClanarinaKategorijaResource\Pages;

use App\Filament\Resources\ClanarinaKategorijaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClanarinaKategorija extends EditRecord
{
    protected static string $resource = ClanarinaKategorijaResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
