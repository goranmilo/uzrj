<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sprema;
use App\Models\Zvanje;
use App\Models\Odeljenje;
use App\Models\ClanarinaKategorija;

class SifarnikSeeder extends Seeder
{
    public function run(): void
    {
        // Stručne spreme
        $spreme = ['VSS', 'VŠS', 'VMS', 'VKV', 'KV', 'SSS'];
        foreach ($spreme as $i => $s) {
            Sprema::create(['naziv' => $s, 'redosled' => $i + 1]);
        }

        // Zvanja
        $zvanja = [
            'Medicinska sestra', 'Medicinski tehničar', 'Fizioterapeut',
            'Radiološki tehničar', 'Laboratorijski tehničar', 'Farmaceutski tehničar',
            'Sanitarni tehničar', 'Zubni tehničar', 'Nutricionista',
            'Defektolog', 'Logoped', 'Socijalni radnik',
        ];
        foreach ($zvanja as $i => $z) {
            Zvanje::create(['naziv' => $z, 'redosled' => $i + 1]);
        }

        // Odeljenja (primer)
        $odeljenja = [
            'Opšta medicina', 'Hirurgija', 'Internistička', 'Pedijatrija',
            'Ginekologija', 'Psihijatrija', 'Ortopedija', 'Oftalmologija',
            'ORL', 'Dermatologija', 'Urologija', 'Neurologija', 'Kardiologija',
        ];
        foreach ($odeljenja as $i => $o) {
            Odeljenje::create(['naziv' => $o, 'redosled' => $i + 1]);
        }

        // Kategorije članarine
        $kategorije = [
            ['naziv' => 'Zaposlen', 'iznos' => 3000],
            ['naziv' => 'Nezaposlen', 'iznos' => 1500],
            ['naziv' => 'Penzioner', 'iznos' => 1000],
            ['naziv' => 'Počasni', 'iznos' => 0],
        ];
        foreach ($kategorije as $k) {
            ClanarinaKategorija::create($k);
        }
    }
}
