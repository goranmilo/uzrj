<?php

namespace App\Filament\Widgets;

use App\Models\Clan;
use App\Services\BodoviService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClanBodoviWidget extends StatsOverviewWidget
{
    public ?Clan $record = null;

    protected function getStats(): array
    {
        if (!$this->record) {
            return [];
        }

        $status = BodoviService::statusNapretka($this->record);
        $ukupnoPrisustava = $this->record->prisustva()->where('prisutan', true)->count();

        return [
            Stat::make('Ukupno prisustava', $ukupnoPrisustava)
                ->description('Na edukacijama')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),

            Stat::make('Bodovi (godina)', $status['bodovi_godina'] . ' / ' . $status['godisnji_minimum'])
                ->description($status['ispunjava_godisnji'] ? '✓ Minimum ispunjen' : '⚠ Nedostaje ' . $status['preostalo_godina'] . ' bodova')
                ->descriptionIcon($status['ispunjava_godisnji'] ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->color($status['ispunjava_godisnji'] ? 'success' : 'danger'),

            Stat::make('Bodovi (period)', $status['bodovi_period'] . ' / ' . $status['ukupan_prag'])
                ->description($status['procenat_period'] . '% ispunjeno')
                ->descriptionIcon('heroicon-m-star')
                ->color($status['ispunjava_ukupni'] ? 'success' : 'warning'),

            Stat::make('Preostalo do praga', $status['preostalo_period'])
                ->description('Bodova za obnovu licence')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('gray'),
        ];
    }
}
