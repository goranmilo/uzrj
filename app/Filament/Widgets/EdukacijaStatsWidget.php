<?php

namespace App\Filament\Widgets;

use App\Models\Edukacija;
use App\Services\EdukacijaService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EdukacijaStatsWidget extends StatsOverviewWidget
{
    public ?Edukacija $record = null;

    protected function getStats(): array
    {
        if (!$this->record) {
            return [];
        }

        $stats = EdukacijaService::statistike($this->record);

        return [
            Stat::make('Prijavljeni', $stats['prijavljeni'])
                ->description($stats['kapacitet'] ? "od {$stats['kapacitet']} mesta" : 'Bez limita')
                ->color('info'),

            Stat::make('Prisutni', $stats['prisutni'])
                ->description($stats['odsutni'] . ' odsutnih')
                ->color('success'),

            Stat::make('Ukupno bodova', $stats['ukupno_bodova'])
                ->description('Dodeljeno prisutnima')
                ->color('warning'),

            Stat::make('Popunjenost', $stats['popunjenost'] ? $stats['popunjenost'] . '%' : 'N/A')
                ->description('Kapacitet edukacije')
                ->color($stats['popunjenost'] >= 90 ? 'danger' : ($stats['popunjenost'] >= 70 ? 'warning' : 'success')),
        ];
    }
}
