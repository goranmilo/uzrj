<?php

namespace App\Exports;

use App\Models\Clan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters = [];

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = Clan::with(['sprem', 'zvanje', 'odeljenje', 'kategorijaClanarine', 'licence']);

        // Primena filtera
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['kategorija_id'])) {
            $query->where('kategorija_clanarine_id', $this->filters['kategorija_id']);
        }

        if (!empty($this->filters['zvanje_id'])) {
            $query->where('zvanje_id', $this->filters['zvanje_id']);
        }

        return $query->orderBy('prezime')->orderBy('ime')->get();
    }

    public function headings(): array
    {
        return [
            'Članski broj',
            'Ime',
            'Prezime',
            'JMBG',
            'OKG',
            'E-mail',
            'Telefon',
            'Stručna sprema',
            'Zvanje',
            'Odeljenje',
            'Kategorija članarine',
            'Status',
            'Datum učlanjenja',
            'Broj licence',
            'Datum izdavanja licence',
            'Datum isteka licence',
            'Status licence',
        ];
    }

    public function map($clan): array
    {
        $aktivnaLicenca = $clan->licence->first();

        return [
            $clan->clanski_broj,
            $clan->ime,
            $clan->prezime,
            $clan->jmbg,
            $clan->okg,
            $clan->email,
            $clan->telefon,
            $clan->sprem?->naziv,
            $clan->zvanje?->naziv,
            $clan->odeljenje?->naziv,
            $clan->kategorijaClanarine?->naziv,
            $this->mapStatus($clan->status),
            $this->formatDate($clan->datum_uclanjenja),
            $aktivnaLicenca?->broj,
            $this->formatDate($aktivnaLicenca?->datum_izdavanja),
            $this->formatDate($aktivnaLicenca?->datum_isteka),
            $aktivnaLicenca ? $this->mapLicencaStatus($aktivnaLicenca->status) : '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Stil za header
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'FF00B050'],
                ],
            ],
        ];
    }

    private function mapStatus(string $status): string
    {
        return match ($status) {
            'aktivan' => 'Aktivan',
            'neaktivan' => 'Neaktivan',
            'suspendovan' => 'Suspendovan',
            default => $status,
        };
    }

    private function mapLicencaStatus(string $status): string
    {
        return match ($status) {
            'vazeca' => 'Važeća',
            'istice' => 'Ističe',
            'istekla' => 'Istekla',
            default => $status,
        };
    }

    private function formatDate($date): string
    {
        if (empty($date)) {
            return '';
        }

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date instanceof \Carbon\Carbon ? $date->format('d.m.Y') : (string) $date;
    }
}
