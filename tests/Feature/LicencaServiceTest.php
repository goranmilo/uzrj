<?php

namespace Tests\Feature;

use App\Models\Clan;
use App\Services\LicencaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LicencaServiceTest extends TestCase
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

    private function clan(): Clan
    {
        return Clan::create([
            'ime' => 'Ana',
            'prezime' => 'Anić',
            'jmbg' => '0101990500011',
            'status' => 'aktivan',
        ]);
    }

    public function test_kreira_licencu_i_racuna_datum_isteka(): void
    {
        $clan = $this->clan();

        $licenca = LicencaService::sacuvaj($clan, [
            'broj' => 'L-123',
            'datum_izdavanja' => '2024-05-10',
            'datum_isteka' => null,
        ]);

        $this->assertNotNull($licenca);
        $this->assertSame('L-123', $licenca->broj);
        $this->assertSame('2031-05-10', $licenca->datum_isteka->toDateString());
        $this->assertSame('vazeca', $licenca->status);
        $this->assertDatabaseCount('licence', 1);
    }

    public function test_prazan_unos_ne_kreira_licencu(): void
    {
        $clan = $this->clan();

        $this->assertNull(LicencaService::sacuvaj($clan, ['broj' => '', 'datum_izdavanja' => null]));
        $this->assertDatabaseCount('licence', 0);
    }

    public function test_ponovno_cuvanje_azurira_postojecu_licencu(): void
    {
        $clan = $this->clan();

        LicencaService::sacuvaj($clan, ['broj' => 'L-1', 'datum_izdavanja' => '2024-05-10']);
        LicencaService::sacuvaj($clan->fresh(), ['broj' => 'L-2', 'datum_izdavanja' => '2024-06-01']);

        $this->assertDatabaseCount('licence', 1);
        $this->assertDatabaseHas('licence', ['broj' => 'L-2']);
    }

    public function test_status_prati_datum_isteka(): void
    {
        $this->assertSame('vazeca', LicencaService::status(Carbon::parse('2027-01-01')));
        $this->assertSame('istice', LicencaService::status(now()->addDays(30)));
        $this->assertSame('istekla', LicencaService::status(now()->subDay()));
    }

    public function test_podaci_za_formu_vracaju_merodavnu_licencu(): void
    {
        $clan = $this->clan();
        LicencaService::sacuvaj($clan, ['broj' => 'L-9', 'datum_izdavanja' => '2024-05-10']);

        $podaci = LicencaService::podaciZaFormu($clan->fresh());

        $this->assertSame('L-9', $podaci['broj']);
        $this->assertSame('2024-05-10', $podaci['datum_izdavanja']);
        $this->assertSame('2031-05-10', $podaci['datum_isteka']);
    }
}
