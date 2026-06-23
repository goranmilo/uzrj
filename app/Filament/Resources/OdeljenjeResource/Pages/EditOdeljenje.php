<?php

namespace App\Filament\Resources\OdeljenjeResource\Pages;

use App\Filament\Resources\OdeljenjeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOdeljenje extends EditRecord
{
    protected static string $resource = OdeljenjeResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
