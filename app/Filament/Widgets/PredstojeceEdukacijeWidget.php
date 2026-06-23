<?php

namespace App\Filament\Widgets;

use App\Models\Edukacija;
use Filament\Widgets\Widget;

class PredstojeceEdukacijeWidget extends Widget
{
    protected static ?int $sort = 6;
    protected static string $view = 'filament.widgets.predstojece-edukacije-widget';

    public ?array $edukacije = [];

    public function mount(): void
    {
        $this->edukacije = Edukacija::where('status', 'planirana')
            ->where('datum_pocetka', '>=', now())
            ->orderBy('datum_pocetka')
            ->limit(5)
            ->get()
            ->toArray();
    }
}
