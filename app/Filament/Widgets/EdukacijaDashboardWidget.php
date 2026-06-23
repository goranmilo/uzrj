<?php

namespace App\Filament\Widgets;

use App\Models\Edukacija;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class EdukacijaDashboardWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    public function getHeading(): ?string
    {
        return 'Edukacije';
    }

    protected function getStats(): array
    {
        // Predstojeće edukacije
        $predstojece = Edukacija::where('status', 'planirana')
            ->where('datum_pocetka', '>=', now())
            ->count();

        // Održane ove godine
        $odrzaneOveGodine = Edukacija::where('status', 'odrzana')
            ->whereYear('datum_pocetka', now()->year)
            ->count();

        // Ukupno prisustava ove godine
        $ukupnoPrisustava = \App\Models\Prisustvo::whereHas('edukacija', function ($query) {
            $query->whereYear('datum_pocetka', now()->year);
        })
            ->where('prisutan', true)
            ->count();

        // Ukupno dodeljenih bodova
        $ukupnoBodova = \App\Models\Bod::whereYear('datum', now()->year)->sum('bodovi');

        // Sledeća edukacija
        $sledeca = Edukacija::where('status', 'planirana')
            ->where('datum_pocetka', '>=', now())
            ->orderBy('datum_pocetka')
            ->first();

        return [
            Stat::make('Predstojeće edukacije', $predstojece)
                ->description($sledeca ? 'Sledeća: ' . $sledeca->datum_pocetka->format('d.m.Y') : 'Nema zakazanih')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Održane ove godine', $odrzaneOveGodine)
                ->description($ukupnoPrisustava . ' prisustava')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Dodeljeno bodova', number_format($ukupnoBodova, 0))
                ->description('U tekućoj godini')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
        ];
    }
}
