<?php

namespace App\Filament\Widgets;

use App\Models\Clan;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ClanTrendChart extends ChartWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): ?string
    {
        return 'Trend učlanjenja';
    }

    protected function getMaxHeight(): ?string
    {
        return '300px';
    }

    protected function getData(): array
    {
        $months = collect();
        $data = collect();

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months->push($date->format('M Y'));
            
            $count = Clan::whereYear('datum_uclanjenja', $date->year)
                ->whereMonth('datum_uclanjenja', $date->month)
                ->count();
            
            $data->push($count);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Novi članovi',
                    'data' => $data->toArray(),
                    'borderColor' => '#10B981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
