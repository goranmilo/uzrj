<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClanImportTemplate implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function collection(): Collection
    {
        // Prazan red za primer
        return collect([
            [
                'Marko',           // ime
                'Petrović',        // prezime
                '1234567890123',   // jmbg
                'marko@email.com', // email
                '+38164123456',    // telefon
                '12345',           // okg
                'VSS',             // sprema
                'Medicinska sestra', // zvanje
                'Hirurgija',       // odeljenje
                'Zaposlen',        // kategorija_clanarine
                'aktivan',         // status
                '01.01.2024',      // datum_uclanjenja
                '123/24',          // clanski_broj
                'L-123456',        // licenca_broj
                '01.01.2024',      // licenca_datum_izdavanja
                '01.01.2031',      // licenca_datum_isteka
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'ime',
            'prezime',
            'jmbg',
            'email',
            'telefon',
            'okg',
            'sprema',
            'zvanje',
            'odeljenje',
            'kategorija_clanarine',
            'status',
            'datum_uclanjenja',
            'clanski_broj',
            'licenca_broj',
            'licenca_datum_izdavanja',
            'licenca_datum_isteka',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'FF4472C4'],
                ],
                'font' => [
                    'color' => ['argb' => 'FFFFFFFF'],
                    'bold' => true,
                ],
            ],
        ];
    }
}
