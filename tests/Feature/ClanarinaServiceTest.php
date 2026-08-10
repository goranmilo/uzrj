<?php

namespace Tests\Feature;

use App\Models\Clan;
use App\Models\ClanarinaKategorija;
use App\Models\ClanarinaPeriod;
use App\Services\ClanarinaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ClanarinaServiceTest extends TestCase
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

    private function kategorija(float $iznos = 1000): ClanarinaKategorija
    {
        return ClanarinaKategorija::create([
            'naziv' => 'Redovna',
            'iznos' => $iznos,
            'aktivno' => true,
        ]);
    }

    private function period(): ClanarinaPeriod
    {
        return ClanarinaPeriod::create([
            'naziv' => 'Godina 2026',
            'vrsta' => 'godisnje',
            'vazi_od' => '2026-01-01',
            'vazi_do' => '2026-12-31',
            'aktivan' => true,
        ]);
    }

    private function clan(ClanarinaKategorija $kategorija, string $datumUclanjenja): Clan
    {
        return Clan::create([
            'ime' => 'Ana',
            'prezime' => 'Anić',
            'jmbg' => '0101990500011',
            'status' => 'aktivan',
            'kategorija_clanarine_id' => $kategorija->id,
            'datum_uclanjenja' => $datumUclanjenja,
        ]);
    }

    public function test_clan_uclanjen_pre_pocetka_perioda_placa_pun_iznos(): void
    {
        $kategorija = $this->kategorija();
        $clan = $this->clan($kategorija, '2020-03-01');

        $clanarina = ClanarinaService::zaduziClana($clan, $this->period());

        $this->assertEqualsWithDelta(1000, (float) $clanarina->iznos_zaduzenja, 0.01);
        $this->assertSame('dug', $clanarina->status);
    }

    public function test_pro_rata_umanjuje_zaduzenje_za_upis_usred_perioda(): void
    {
        $kategorija = $this->kategorija();
        $clan = $this->clan($kategorija, '2026-07-01');

        $clanarina = ClanarinaService::zaduziClana($clan, $this->period());

        // 183 od 364 dana perioda preostaje nakon 1. jula.
        $this->assertEqualsWithDelta(502.75, (float) $clanarina->iznos_zaduzenja, 0.01);
    }

    public function test_dvostruko_zaduzenje_za_isti_period_nije_dozvoljeno(): void
    {
        $kategorija = $this->kategorija();
        $clan = $this->clan($kategorija, '2020-03-01');
        $period = $this->period();

        ClanarinaService::zaduziClana($clan, $period);

        $this->expectException(\Exception::class);
        ClanarinaService::zaduziClana($clan, $period);
    }

    public function test_uplata_azurira_status_i_dugovanje(): void
    {
        $kategorija = $this->kategorija();
        $clan = $this->clan($kategorija, '2020-03-01');
        $clanarina = ClanarinaService::zaduziClana($clan, $this->period());

        ClanarinaService::evidentirajUplatu($clanarina, 400);
        $clanarina->refresh();

        $this->assertSame('delimicno', $clanarina->status);
        $this->assertEqualsWithDelta(600, ClanarinaService::ukupnoDugovanje($clan), 0.01);

        ClanarinaService::evidentirajUplatu($clanarina, 600);
        $clanarina->refresh();

        $this->assertSame('placeno', $clanarina->status);
        $this->assertEqualsWithDelta(0, ClanarinaService::ukupnoDugovanje($clan), 0.01);
    }
}
