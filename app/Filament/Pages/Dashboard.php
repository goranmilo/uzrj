<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AktuelnostiWidget;
use App\Filament\Widgets\ClanStatsOverview;
use App\Filament\Widgets\ClanTrendChart;
use App\Filament\Widgets\EdukacijaDashboardWidget;
use App\Filament\Widgets\FinansijskiPregled;
use App\Filament\Widgets\PredstojeceEdukacijeWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Dashboard';
    protected static ?int $navigationSort = -1;

    protected function getHeaderWidgets(): array
    {
        return [
            ClanStatsOverview::class,
            FinansijskiPregled::class,
            EdukacijaDashboardWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            ClanTrendChart::class,
            PredstojeceEdukacijeWidget::class,
            AktuelnostiWidget::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            AccountWidget::class,
        ];
    }

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }
}
