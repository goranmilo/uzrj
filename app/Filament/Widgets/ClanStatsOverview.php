<?php

namespace App\Filament\Widgets;

use App\Models\Clan;
use App\Models\Clanarina;
use App\Models\Edukacija;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClanStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public function getHeading(): ?string
    {
        return 'Pregled članstva';
    }

    protected function getStats(): array
    {
        $ukupnoClanova = Clan::count();
        $aktivnih = Clan::where('status', 'aktivan')->count();
        $neaktivnih = Clan::where('status', 'neaktivan')->count();
        $suspendovanih = Clan::where('status', 'suspendovan')->count();

        $ukupnoZaduzeno = Clanarina::sum('iznos_zaduzenja');
        $ukupnoPlaceno = Clanarina::sum('iznos_placen');
        $dug = $ukupnoZaduzeno - $ukupnoPlaceno;

        $predstojeceEdukacije = Edukacija::where('status', 'planirana')
            ->where('datum_pocetka', '>=', now())
            ->count();

        $clanoviBezMajl = Clan::whereNull('email')->count();

        return [
            Stat::make('Ukupno članova', $ukupnoClanova)
                ->description("{$aktivnih} aktivnih")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5]),

            Stat::make('Neaktivni / Suspendovani', "{$neaktivnih} / {$suspendovanih}")
                ->description('Članovi bez aktivnog statusa')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('warning'),

            Stat::make('Ukupan dug', number_format($dug, 0, ',', '.') . ' RSD')
                ->description(number_format($ukupnoPlaceno, 0, ',', '.') . ' RSD naplaćeno')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($dug > 0 ? 'danger' : 'success'),

            Stat::make('Predstojeće edukacije', $predstojeceEdukacije)
                ->description('Planirane u narednom periodu')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),

            Stat::make('Članovi bez e-maila', $clanoviBezMajl)
                ->description('Potrebno ažurirati kontakt')
                ->descriptionIcon('heroicon-m-envelope')
                ->color($clanoviBezMajl > 0 ? 'warning' : 'success'),
        ];
    }
}
