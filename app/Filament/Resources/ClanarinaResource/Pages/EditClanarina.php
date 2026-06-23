<?php

namespace App\Filament\Resources\ClanarinaResource\Pages;

use App\Filament\Resources\ClanarinaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClanarina extends EditRecord
{
    protected static string $resource = ClanarinaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
