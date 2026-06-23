<?php

namespace App\Filament\Widgets;

use App\Models\Aktuelnost;
use Filament\Widgets\Widget;
use Illuminate\Support\HtmlString;

class AktuelnostiWidget extends Widget
{
    protected static ?int $sort = 5;
    protected static string $view = 'filament.widgets.aktuelnosti-widget';

    public ?array $aktuelnosti = [];

    public function mount(): void
    {
        $this->aktuelnosti = Aktuelnost::where('objavljeno', true)
            ->orderBy('datum_objave', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }
}
