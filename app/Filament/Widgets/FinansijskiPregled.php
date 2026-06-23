<?php

namespace App\Filament\Widgets;

use App\Models\Clanarina;
use App\Models\Uplata;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class FinansijskiPregled extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    public function getHeading(): ?string
    {
        return 'Finansijski pregled';
    }

    protected function getStats(): array
    {
        // Ukupno zaduženo
        $ukupnoZaduzeno = Clanarina::sum('iznos_zaduzenja');
        
        // Ukupno naplaćeno
        $ukupnoNaplaceno = Clanarina::sum('iznos_placen');
        
        // Ukupan dug
        $ukupanDug = $ukupnoZaduzeno - $ukupnoNaplaceno;
        
        // Procenat naplate
        $procenatNaplate = $ukupnoZaduzeno > 0 
            ? round(($ukupnoNaplaceno / $ukupnoZaduzeno) * 100, 1)
            : 0;

        // Uplate ovog meseca
        $ovajMesec = Uplata::whereMonth('datum', Carbon::now()->month)
            ->whereYear('datum', Carbon::now()->year)
            ->sum('iznos');

        // Uplate prošlog meseca
        $prosliMesec = Uplata::whereMonth('datum', Carbon::now()->subMonth()->month)
            ->whereYear('datum', Carbon::now()->subMonth()->year)
            ->sum('iznos');

        // Trend
        $trend = $prosliMesec > 0 
            ? round((($ovajMesec - $prosliMesec) / $prosliMesec) * 100, 1)
            : 0;

        // Broj dužnika
        $brojDuznika = Clanarina::where('status', '!=', 'placeno')
            ->distinct('clan_id')
            ->count('clan_id');

        return [
            Stat::make('Ukupno naplaćeno', number_format($ukupnoNaplaceno, 0, ',', '.') . ' RSD')
                ->description("{$procenatNaplate}% od ukupnog zaduženja")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($procenatNaplate >= 80 ? 'success' : ($procenatNaplate >= 50 ? 'warning' : 'danger')),

            Stat::make('Ukupan dug', number_format($ukupanDug, 0, ',', '.') . ' RSD')
                ->description("{$brojDuznika} dužnika")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($ukupanDug > 0 ? 'danger' : 'success'),

            Stat::make('Uplate ovog meseca', number_format($ovajMesec, 0, ',', '.') . ' RSD')
                ->description(($trend >= 0 ? '+' : '') . "{$trend}% u odnosu na prošli mesec")
                ->descriptionIcon($trend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($trend >= 0 ? 'success' : 'danger'),

            Stat::make('Zaduženja (plaćeno)', 
                Clanarina::where('status', 'placeno')->count() . '/' . Clanarina::count()
            )
                ->description('Broj plaćenih zaduženja')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
        ];
    }
}
