<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClanarinaPeriod;
use App\Models\Clanarina;
use App\Models\Clan;
use App\Services\ClanarinaService;

class FinansijeSeeder extends Seeder
{
    public function run(): void
    {
        // Kreiranje perioda članarine
        $period2025 = ClanarinaPeriod::create([
            'naziv' => '2025. godina',
            'vrsta' => 'godisnje',
            'vazi_od' => '2025-01-01',
            'vazi_do' => '2025-12-31',
            'aktivan' => false,
        ]);

        $period2026 = ClanarinaPeriod::create([
            'naziv' => '2026. godina',
            'vrsta' => 'godisnje',
            'vazi_od' => '2026-01-01',
            'vazi_do' => '2026-12-31',
            'aktivan' => true,
        ]);

        // Zaduženje za postojeće članove
        $clanovi = Clan::where('status', 'aktivan')->get();
        
        foreach ($clanovi as $clan) {
            try {
                ClanarinaService::zaduziClana($clan, $period2026);
            } catch (\Exception $e) {
                // Član možda već ima zaduženje
            }
        }
    }
}
