<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Podesavanje;

class PodesavanjaSeeder extends Seeder
{
    public function run(): void
    {
        // Bodovni sistem
        Podesavanje::set('godisnji_prag_bodova', 20, 'integer');
        Podesavanje::set('ukupan_prag_bodova', 140, 'integer');
        Podesavanje::set('licencni_period_god', 7, 'integer');
        Podesavanje::set('dani_pre_isteka_upozorenje', 60, 'integer');

        // Članarina
        Podesavanje::set('vrsta_naplate_clanarine', 'godisnje', 'string');
        Podesavanje::set('pro_rata_racunanje', true, 'boolean');

        // Organizacija
        Podesavanje::set('naziv_udruzenja', 'Udruženje zdravstvenih radnika', 'string');
        Podesavanje::set('maticni_broj_udruzenja', '', 'string');
        Podesavanje::set('adresa_udruzenja', '', 'string');
        Podesavanje::set('email_udruzenja', '', 'string');
        Podesavanje::set('telefon_udruzenja', '', 'string');
    }
}
