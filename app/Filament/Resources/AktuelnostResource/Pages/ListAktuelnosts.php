<?php

namespace App\Filament\Resources\AktuelnostResource\Pages;

use App\Filament\Resources\AktuelnostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAktuelnosts extends ListRecords
{
    protected static string $resource = AktuelnostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
