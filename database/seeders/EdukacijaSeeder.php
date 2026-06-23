<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Edukacija;

class EdukacijaSeeder extends Seeder
{
    public function run(): void
    {
        // Test edukacija 1
        Edukacija::create([
            'naziv' => 'Osnove prve pomoći',
            'opis' => 'Obuka iz osnova prve pomoći za zdravstvene radnike',
            'datum_pocetka' => now()->addDays(30),
            'datum_zavrsetka' => now()->addDays(30)->addHours(4),
            'lokacija' => 'Beograd, Dom zdravlja',
            'kapacitet' => 50,
            'predavaci' => 'Dr Dragan Petrović',
            'akreditacioni_broj' => 'KME-2026-001',
            'vrsta_kme' => 'Prva pomoć',
            'bodovi' => 5,
            'ciljna_grupa' => 'Medicinske sestre',
            'status' => 'planirana',
        ]);

        // Test edukacija 2
        Edukacija::create([
            'naziv' => 'Kardiopulmonalna reanimacija',
            'opis' => 'Napredni kurs KPR-a za zdravstvene radnike',
            'datum_pocetka' => now()->addDays(60),
            'datum_zavrsetka' => now()->addDays(60)->addHours(8),
            'lokacija' => 'Novi Sad, Klinički centar',
            'kapacitet' => 30,
            'predavaci' => 'Prof. Dr Milica Jovanović',
            'akreditacioni_broj' => 'KME-2026-002',
            'vrsta_kme' => 'KPR',
            'bodovi' => 10,
            'ciljna_grupa' => 'Svi zdravstveni radnici',
            'status' => 'planirana',
        ]);

        // Test edukacija 3 (održana)
        Edukacija::create([
            'naziv' => 'Higijena i prevencija infekcija',
            'opis' => 'Standardne mere prevencije u zdravstvenim ustanovama',
            'datum_pocetka' => now()->subDays(30),
            'datum_zavrsetka' => now()->subDays(30)->addHours(6),
            'lokacija' => 'Beograd, Hotel Metropol',
            'kapacitet' => 100,
            'predavaci' => 'Dr Nikola Antić',
            'akreditacioni_broj' => 'KME-2025-010',
            'vrsta_kme' => 'Higijena',
            'bodovi' => 8,
            'ciljna_grupa' => 'Medicinske sestre i tehničari',
            'status' => 'odrzana',
        ]);
    }
}
