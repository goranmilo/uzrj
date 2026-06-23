<?php

namespace App\Imports;

use App\Models\Clan;
use App\Models\Licenca;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ClanImport implements ToCollection, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                DB::beginTransaction();

                // Mapiranje polja iz Excel-a na naš model
                $clanData = [
                    'ime' => $row['ime'] ?? '',
                    'prezime' => $row['prezime'] ?? '',
                    'jmbg' => $this->formatJmbg($row['jmbg'] ?? ''),
                    'email' => $row['email'] ?? null,
                    'telefon' => $row['telefon'] ?? null,
                    'okg' => $row['okg'] ?? null,
                    'status' => $this->mapStatus($row['status'] ?? 'aktivan'),
                    'datum_uclanjenja' => $this->parseDate($row['datum_uclanjenja'] ?? null),
                    'clanski_broj' => $row['clanski_broj'] ?? null,
                ];

                // Mapiranje šifarnika (po imenu)
                if (!empty($row['sprema'])) {
                    $clanData['sprema_id'] = $this->findOrCreateSprema($row['sprema']);
                }

                if (!empty($row['zvanje'])) {
                    $clanData['zvanje_id'] = $this->findOrCreateZvanje($row['zvanje']);
                }

                if (!empty($row['odeljenje'])) {
                    $clanData['odeljenje_id'] = $this->findOrCreateOdeljenje($row['odeljenje']);
                }

                if (!empty($row['kategorija_clanarine'])) {
                    $clanData['kategorija_clanarine_id'] = $this->findKategorija($row['kategorija_clanarine']);
                }

                // Kreiranje ili ažuriranje člana
                $clan = Clan::updateOrCreate(
                    ['jmbg' => $clanData['jmbg']],
                    $clanData
                );

                // Kreiranje licence ako postoji
                if (!empty($row['licenca_broj'])) {
                    $this->createOrUpdateLicenca($clan, $row);
                }

                DB::commit();
                $this->importedCount++;

            } catch (\Exception $e) {
                DB::rollBack();
                $this->skippedCount++;
                $this->errors[] = [
                    'row' => $row->keys()->first() ?? 'N/A',
                    'jmbg' => $row['jmbg'] ?? 'N/A',
                    'error' => $e->getMessage(),
                ];
                Log::error('Greška pri importu člana', [
                    'jmbg' => $row['jmbg'] ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'ime' => 'required|string|max:255',
            'prezime' => 'required|string|max:255',
            'jmbg' => 'required|string|size:13',
            'email' => 'nullable|email|max:255',
            'telefon' => 'nullable|string|max:20',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    // Helper metode

    private function formatJmbg(string $jmbg): string
    {
        $jmbg = preg_replace('/[^0-9]/', '', $jmbg);
        return str_pad($jmbg, 13, '0', STR_PAD_LEFT);
    }

    private function mapStatus(string $status): string
    {
        $statusMap = [
            'aktivan' => 'aktivan',
            'active' => 'aktivan',
            'neaktivan' => 'neaktivan',
            'inactive' => 'neaktivan',
            'suspendovan' => 'suspendovan',
            'suspended' => 'suspendovan',
        ];

        return $statusMap[strtolower($status)] ?? 'aktivan';
    }

    private function parseDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        // Pokušaj različite formate
        $formats = ['d.m.Y', 'Y-m-d', 'd/m/Y', 'd-m-Y'];

        foreach ($formats as $format) {
            $parsed = \Carbon\Carbon::createFromFormat($format, $date);
            if ($parsed) {
                return $parsed->format('Y-m-d');
            }
        }

        return null;
    }

    private function findOrCreateSprema(string $naziv): int
    {
        return \App\Models\Sprema::firstOrCreate(
            ['naziv' => trim($naziv)],
            ['aktivno' => true, 'redosled' => 0]
        )->id;
    }

    private function findOrCreateZvanje(string $naziv): int
    {
        return \App\Models\Zvanje::firstOrCreate(
            ['naziv' => trim($naziv)],
            ['aktivno' => true, 'redosled' => 0]
        )->id;
    }

    private function findOrCreateOdeljenje(string $naziv): int
    {
        return \App\Models\Odeljenje::firstOrCreate(
            ['naziv' => trim($naziv)],
            ['aktivno' => true, 'redosled' => 0]
        )->id;
    }

    private function findKategorija(string $naziv): ?int
    {
        $kategorija = \App\Models\ClanarinaKategorija::where('naziv', 'like', trim($naziv))->first();
        return $kategorija?->id;
    }

    private function createOrUpdateLicenca(Clan $clan, array $row): void
    {
        $datumIzdavanja = $this->parseDate($row['licenca_datum_izdavanja'] ?? null);
        $datumIsteka = $this->parseDate($row['licenca_datum_isteka'] ?? null);

        // Automatski računaj datum isteka ako nije definisan (+7 godina)
        if ($datumIzdavanja && !$datumIsteka) {
            $datumIsteka = \Carbon\Carbon::parse($datumIzdavanja)->addYears(7)->format('Y-m-d');
        }

        // Odredi status licence
        $status = 'vazeca';
        if ($datumIsteka) {
            $istek = \Carbon\Carbon::parse($datumIsteka);
            if ($istek->isPast()) {
                $status = 'istekla';
            } elseif ($istek->diffInDays(now()) <= 60) {
                $status = 'istice';
            }
        }

        Licenca::updateOrCreate(
            ['clan_id' => $clan->id],
            [
                'broj' => $row['licenca_broj'],
                'datum_izdavanja' => $datumIzdavanja,
                'datum_isteka' => $datumIsteka,
                'status' => $status,
            ]
        );
    }

    // Getter za rezultate importa
    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
