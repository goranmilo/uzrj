<?php

namespace App\Filament\Resources\UplataResource\Pages;

use App\Filament\Resources\UplataResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUplata extends EditRecord
{
    protected static string $resource = UplataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
