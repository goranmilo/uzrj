<?php

namespace App\Imports;

use App\Models\Clan;
use App\Models\ClanarinaKategorija;
use App\Models\Licenca;
use App\Models\Odeljenje;
use App\Models\Sprema;
use App\Models\Zvanje;
use App\Rules\Jmbg;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ClanImport implements SkipsEmptyRows, SkipsOnFailure, ToCollection, WithBatchInserts, WithChunkReading, WithHeadingRow, WithValidation
{
    protected $importedCount = 0;

    protected $skippedCount = 0;

    protected $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $row = $this->normalizuj($row);

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
                if (! empty($row['sprema'])) {
                    $clanData['sprema_id'] = $this->findOrCreateSprema($row['sprema']);
                }

                if (! empty($row['zvanje'])) {
                    $clanData['zvanje_id'] = $this->findOrCreateZvanje($row['zvanje']);
                }

                if (! empty($row['odeljenje'])) {
                    $clanData['odeljenje_id'] = $this->findOrCreateOdeljenje($row['odeljenje']);
                }

                if (! empty($row['kategorija_clanarine'])) {
                    $clanData['kategorija_clanarine_id'] = $this->findKategorija($row['kategorija_clanarine']);
                }

                // Kreiranje ili ažuriranje člana
                $clan = Clan::updateOrCreate(
                    ['jmbg' => $clanData['jmbg']],
                    $clanData
                );

                // Kreiranje licence ako postoji
                if (! empty($row['licenca_broj'])) {
                    $this->createOrUpdateLicenca($clan, $row);
                }

                DB::commit();
                $this->importedCount++;

            } catch (\Throwable $e) {
                DB::rollBack();
                $this->skippedCount++;
                $this->errors[] = [
                    'row' => null,
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
            // Ista provera kontrolne cifre kao u formi za unos člana.
            'jmbg' => ['required', 'string', 'size:13', new Jmbg],
            'email' => 'nullable|email|max:255',
            'telefon' => 'nullable|string|max:20',
        ];
    }

    /**
     * Red koji ne prođe validaciju se preskače i prijavljuje, umesto da
     * prekine ceo uvoz.
     */
    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $this->skippedCount++;
            $this->errors[] = [
                'row' => $failure->row(),
                'jmbg' => $failure->values()['jmbg'] ?? 'N/A',
                'error' => implode(' ', $failure->errors()),
            ];

            Log::warning('Red preskočen pri importu članova', [
                'row' => $failure->row(),
                'errors' => $failure->errors(),
            ]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function customValidationMessages(): array
    {
        return [
            'jmbg.size' => 'JMBG mora imati 13 cifara.',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForValidation($data, $index)
    {
        return $this->normalizuj($data);
    }

    /**
     * Dovedi red iz tabele u oblik koji očekuju validacija i upis.
     *
     * - numeričke ćelije (JMBG, telefon, brojevi) Excel vraća kao brojeve, pa se
     *   vraćaju u tekst; JMBG dobija vodeću nulu
     * - ako je prezime prazno, a u koloni „ime" stoji puno ime, poslednja reč se
     *   uzima kao prezime
     *
     * @param  array<string, mixed>|Collection  $row
     * @return array<string, mixed>
     */
    protected function normalizuj(array|Collection $row): array
    {
        $row = $row instanceof Collection ? $row->toArray() : $row;

        foreach ($row as $kljuc => $vrednost) {
            if (is_string($vrednost)) {
                $vrednost = trim($vrednost);
                $row[$kljuc] = $vrednost === '' ? null : $vrednost;
            }
        }

        foreach (['jmbg', 'telefon', 'okg', 'clanski_broj', 'licenca_broj'] as $polje) {
            if (isset($row[$polje]) && is_numeric($row[$polje])) {
                $row[$polje] = (string) $row[$polje];
            }
        }

        if (filled($row['jmbg'] ?? null)) {
            $row['jmbg'] = $this->formatJmbg((string) $row['jmbg']);
        }

        if (blank($row['prezime'] ?? null) && filled($row['ime'] ?? null)) {
            $delovi = preg_split('/\s+/', trim((string) $row['ime']));

            if (count($delovi) > 1) {
                $row['prezime'] = array_pop($delovi);
                $row['ime'] = implode(' ', $delovi);
            }
        }

        return $row;
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

    private function parseDate(mixed $date): ?string
    {
        if (blank($date)) {
            return null;
        }

        if ($date instanceof \DateTimeInterface) {
            return Carbon::instance($date)->format('Y-m-d');
        }

        // Excel čuva datume kao redni broj dana od 1900. godine.
        if (is_numeric($date)) {
            try {
                return Carbon::instance(
                    Date::excelToDateTimeObject((float) $date)
                )->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        $date = trim((string) $date);

        // Pokušaj različite formate. Carbon baca izuzetak kad format ne odgovara,
        // pa se svaki pokušaj mora izolovati — inače prvi neodgovarajući format
        // obara ceo red.
        $formats = ['d.m.Y', 'Y-m-d', 'd/m/Y', 'd-m-Y'];

        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $date);
            } catch (\Throwable) {
                continue;
            }

            // createFromFormat je popustljiv (npr. "31.02.2024"), pa se rezultat
            // proverava povratnim formatiranjem.
            if ($parsed && $parsed->format($format) === $date) {
                return $parsed->format('Y-m-d');
            }
        }

        return null;
    }

    private function findOrCreateSprema(string $naziv): int
    {
        return Sprema::firstOrCreate(
            ['naziv' => trim($naziv)],
            ['aktivno' => true, 'redosled' => 0]
        )->id;
    }

    private function findOrCreateZvanje(string $naziv): int
    {
        return Zvanje::firstOrCreate(
            ['naziv' => trim($naziv)],
            ['aktivno' => true, 'redosled' => 0]
        )->id;
    }

    private function findOrCreateOdeljenje(string $naziv): int
    {
        return Odeljenje::firstOrCreate(
            ['naziv' => trim($naziv)],
            ['aktivno' => true, 'redosled' => 0]
        )->id;
    }

    private function findKategorija(string $naziv): ?int
    {
        $kategorija = ClanarinaKategorija::where('naziv', 'like', trim($naziv))->first();

        return $kategorija?->id;
    }

    private function createOrUpdateLicenca(Clan $clan, array $row): void
    {
        $datumIzdavanja = $this->parseDate($row['licenca_datum_izdavanja'] ?? null);
        $datumIsteka = $this->parseDate($row['licenca_datum_isteka'] ?? null);

        // Automatski računaj datum isteka ako nije definisan (+7 godina)
        if ($datumIzdavanja && ! $datumIsteka) {
            $datumIsteka = Carbon::parse($datumIzdavanja)->addYears(7)->format('Y-m-d');
        }

        // Odredi status licence
        $status = 'vazeca';
        if ($datumIsteka) {
            $istek = Carbon::parse($datumIsteka);
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
