<?php

namespace App\Filament\Resources\PodesavanjeResource\Pages;

use App\Filament\Resources\PodesavanjeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPodesavanje extends EditRecord
{
    protected static string $resource = PodesavanjeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
