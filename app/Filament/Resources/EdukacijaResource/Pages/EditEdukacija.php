<?php

namespace App\Filament\Resources\EdukacijaResource\Pages;

use App\Filament\Resources\EdukacijaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEdukacija extends EditRecord
{
    protected static string $resource = EdukacijaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
