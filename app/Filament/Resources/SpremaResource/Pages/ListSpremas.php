<?php

namespace App\Filament\Resources\SpremaResource\Pages;

use App\Filament\Resources\SpremaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSpremas extends ListRecords
{
    protected static string $resource = SpremaResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
