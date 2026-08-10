<?php

namespace Tests\Feature;

use App\Models\Bod;
use App\Models\Clan;
use App\Models\Licenca;
use App\Services\BodoviService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BodoviServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-08-10');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function clan(string $jmbg = '0101990500011'): Clan
    {
        return Clan::create([
            'ime' => 'Ana',
            'prezime' => 'Anić',
            'jmbg' => $jmbg,
            'status' => 'aktivan',
            'datum_uclanjenja' => '2019-04-01',
        ]);
    }

    private function licenca(Clan $clan, string $datumIzdavanja): Licenca
    {
        return $clan->licence()->create([
            'broj' => 'L-1',
            'datum_izdavanja' => $datumIzdavanja,
            'datum_isteka' => Carbon::parse($datumIzdavanja)->addYears(7),
            'status' => 'vazeca',
        ]);
    }

    public function test_licencni_period_se_pomera_nakon_isteka(): void
    {
        $clan = $this->clan();
        $this->licenca($clan, '2019-04-01');

        // 2019 + 7 = 2026-04-01 je već prošlo → tekući period počinje tada.
        $this->assertSame(
            '2026-04-01',
            BodoviService::pocetakLicencnogPerioda($clan->fresh())->toDateString(),
        );
    }

    public function test_licencna_godina_se_racuna_od_datuma_izdavanja_a_ne_od_januara(): void
    {
        $clan = $this->clan();
        $this->licenca($clan, '2023-09-15');
        $clan = $clan->fresh();

        $this->assertSame('2023-09-15', BodoviService::pocetakLicencnogPerioda($clan)->toDateString());
        $this->assertSame('2025-09-15', BodoviService::pocetakLicencneGodine($clan)->toDateString());
        $this->assertSame(3, BodoviService::redniBrojLicencneGodine($clan));
        $this->assertSame(2025, BodoviService::licencnaGodina($clan));
    }

    public function test_bodovi_se_ne_prenose_izmedju_licencnih_godina(): void
    {
        $clan = $this->clan();
        $this->licenca($clan, '2023-09-15');
        $clan = $clan->fresh();

        $this->bod($clan, '2025-08-01', 5);   // prethodna licencna godina
        $this->bod($clan, '2025-10-01', 10);  // tekuća licencna godina
        $this->bod($clan, '2026-08-01', 7);   // tekuća licencna godina

        $this->assertSame(17.0, BodoviService::bodoviTekucaGodina($clan));
        $this->assertSame(22.0, BodoviService::ukupnoBodovaPeriod($clan));
    }

    public function test_bodovi_van_tekuceg_licencnog_perioda_se_ne_racunaju(): void
    {
        $clan = $this->clan();
        $this->licenca($clan, '2019-04-01');
        $clan = $clan->fresh();

        $this->bod($clan, '2025-05-01', 30); // prethodni licencni period
        $this->bod($clan, '2026-05-01', 12); // tekući period i tekuća godina

        $this->assertSame(12.0, BodoviService::ukupnoBodovaPeriod($clan));
        $this->assertSame(12.0, BodoviService::bodoviTekucaGodina($clan));
    }

    public function test_clan_bez_licence_koristi_kalendarsku_godinu(): void
    {
        $clan = $this->clan();

        $this->bod($clan, '2025-12-01', 8);
        $this->bod($clan, '2026-02-01', 9);

        $this->assertNull(BodoviService::pocetakLicencneGodine($clan));
        $this->assertSame(2026, BodoviService::licencnaGodina($clan));
        $this->assertSame(9.0, BodoviService::bodoviTekucaGodina($clan));
    }

    public function test_status_napretka_koristi_konfigurisane_pragove(): void
    {
        $clan = $this->clan();
        $this->licenca($clan, '2023-09-15');
        $clan = $clan->fresh();

        $this->bod($clan, '2026-01-15', 10);

        $status = BodoviService::statusNapretka($clan);

        $this->assertSame(10.0, $status['bodovi_godina']);
        $this->assertSame(20, $status['godisnji_minimum']);
        $this->assertFalse($status['ispunjava_godisnji']);
        $this->assertSame(10.0, $status['preostalo_godina']);
    }

    private function bod(Clan $clan, string $datum, float $bodovi): Bod
    {
        return Bod::create([
            'clan_id' => $clan->id,
            'bodovi' => $bodovi,
            'licencna_godina' => BodoviService::licencnaGodina($clan, $datum),
            'datum' => $datum,
            'razlog' => 'Test',
        ]);
    }
}
