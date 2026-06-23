<?php

namespace App\Filament\Resources\ZvanjeResource\Pages;

use App\Filament\Resources\ZvanjeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditZvanje extends EditRecord
{
    protected static string $resource = ZvanjeResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
