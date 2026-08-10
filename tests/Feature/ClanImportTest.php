<?php

namespace Tests\Feature;

use App\Imports\ClanImport;
use App\Models\Clan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Tests\TestCase;

class ClanImportTest extends TestCase
{
    use RefreshDatabase;

    private string $putanja;

    protected function tearDown(): void
    {
        if (isset($this->putanja) && file_exists($this->putanja)) {
            unlink($this->putanja);
        }

        parent::tearDown();
    }

    private function csv(string $sadrzaj): string
    {
        $this->putanja = tempnam(sys_get_temp_dir(), 'clanovi').'.csv';
        file_put_contents($this->putanja, $sadrzaj);

        return $this->putanja;
    }

    public function test_uvozi_clanove_iz_csv_fajla(): void
    {
        $putanja = $this->csv(<<<'CSV'
        ime,prezime,jmbg,email,telefon,status,datum_uclanjenja
        Ana,Anić,0101990500011,ana@test.rs,0601112223,aktivan,15.03.2024
        Marko,Marković,1503857800120,marko@test.rs,0641112223,aktivan,01.06.2024
        CSV);

        $import = new ClanImport;
        Excel::import($import, $putanja, null, ExcelFormat::CSV);

        $this->assertSame(2, $import->getImportedCount());
        $this->assertSame(0, $import->getSkippedCount());
        $this->assertDatabaseHas('clanovi', [
            'jmbg' => '0101990500011',
            'prezime' => 'Anić',
            'datum_uclanjenja' => '2024-03-15',
        ]);
    }

    public function test_prihvata_i_datume_u_iso_formatu(): void
    {
        $putanja = $this->csv(<<<'CSV'
        ime,prezime,jmbg,datum_uclanjenja
        Ana,Anić,0101990500011,2024-03-15
        CSV);

        $import = new ClanImport;
        Excel::import($import, $putanja, null, ExcelFormat::CSV);

        $this->assertSame(1, $import->getImportedCount());
        $this->assertSame('2024-03-15', Clan::firstOrFail()->datum_uclanjenja);
    }

    public function test_neispravan_datum_ne_obara_ceo_red(): void
    {
        $putanja = $this->csv(<<<'CSV'
        ime,prezime,jmbg,datum_uclanjenja
        Ana,Anić,0101990500011,nije-datum
        CSV);

        $import = new ClanImport;
        Excel::import($import, $putanja, null, ExcelFormat::CSV);

        $this->assertSame(1, $import->getImportedCount());
        $this->assertNull(Clan::firstOrFail()->datum_uclanjenja);
    }

    public function test_uvozi_datum_zapisan_kao_excel_redni_broj(): void
    {
        $spreadsheet = new Spreadsheet;
        $list = $spreadsheet->getActiveSheet();
        $list->fromArray([
            ['ime', 'prezime', 'jmbg', 'datum_uclanjenja'],
            ['Ana', 'Anić', '0101990500011', 45366], // 15.03.2024. u Excel zapisu
        ]);

        $this->putanja = tempnam(sys_get_temp_dir(), 'clanovi').'.xlsx';
        (new XlsxWriter($spreadsheet))->save($this->putanja);

        $import = new ClanImport;
        Excel::import($import, $this->putanja, null, ExcelFormat::XLSX);

        $this->assertSame(1, $import->getImportedCount());
        $this->assertSame('2024-03-15', Clan::firstOrFail()->datum_uclanjenja);
    }

    public function test_jmbg_zapisan_kao_broj_prolazi_validaciju(): void
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray([
            ['ime', 'prezime', 'jmbg', 'telefon'],
            ['Marko', 'Marković', 1503857800120, 641112223],
        ]);

        $this->putanja = tempnam(sys_get_temp_dir(), 'clanovi').'.xlsx';
        (new XlsxWriter($spreadsheet))->save($this->putanja);

        $import = new ClanImport;
        Excel::import($import, $this->putanja, null, ExcelFormat::XLSX);

        $this->assertSame(1, $import->getImportedCount());
        $this->assertSame([], $import->getErrors());
        $this->assertDatabaseHas('clanovi', ['jmbg' => '1503857800120']);
    }

    public function test_red_sa_neispravnim_jmbg_se_preskace_a_ostali_prolaze(): void
    {
        $putanja = $this->csv(<<<'CSV'
        ime,prezime,jmbg
        Ana,Anić,0101990500011
        Pera,Perić,0101990500012
        Marko,Marković,1503857800120
        CSV);

        $import = new ClanImport;
        Excel::import($import, $putanja, null, ExcelFormat::CSV);

        $this->assertSame(2, $import->getImportedCount());
        $this->assertSame(1, $import->getSkippedCount());
        $this->assertDatabaseCount('clanovi', 2);
        $this->assertDatabaseMissing('clanovi', ['jmbg' => '0101990500012']);

        $greske = $import->getErrors();
        $this->assertCount(1, $greske);
        $this->assertStringContainsString('JMBG nije ispravan', $greske[0]['error']);
    }

    public function test_ponovni_uvoz_azurira_postojeceg_clana_po_jmbg(): void
    {
        $putanja = $this->csv(<<<'CSV'
        ime,prezime,jmbg
        Ana,Anić,0101990500011
        CSV);

        Excel::import(new ClanImport, $putanja, null, ExcelFormat::CSV);

        $putanja = $this->csv(<<<'CSV'
        ime,prezime,jmbg
        Ana,Perić,0101990500011
        CSV);

        Excel::import(new ClanImport, $putanja, null, ExcelFormat::CSV);

        $this->assertDatabaseCount('clanovi', 1);
        $this->assertSame('Perić', Clan::firstOrFail()->prezime);
    }
}
