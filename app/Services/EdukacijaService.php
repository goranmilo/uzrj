<?php

namespace App\Services;

use App\Models\Bod;
use App\Models\Edukacija;
use App\Models\Prisustvo;
use App\Models\Podesavanje;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EdukacijaService
{
    /**
     * Prijavi člana na edukaciju i generiši QR kod.
     * 
     * @param Edukacija $edukacija
     * @param int $clanId
     * @return Prisustvo
     */
    public static function prijaviClana(Edukacija $edukacija, int $clanId): Prisustvo
    {
        // Provera kapaciteta
        if ($edukacija->kapacitet) {
            $prijavljeni = $edukacija->prisustva()->where('prijavljen', true)->count();
            if ($prijavljeni >= $edukacija->kapacitet) {
                throw new \Exception('Edukacija je popunjena. Kapacitet: ' . $edukacija->kapacitet);
            }
        }

        // Kreiranje ili ažuriranje prisustva
        $prisustvo = Prisustvo::updateOrCreate(
            ['edukacija_id' => $edukacija->id, 'clan_id' => $clanId],
            [
                'prijavljen' => true,
                'qr_token' => Str::uuid(),
                'qr_poslat_at' => null,
            ]
        );

        return $prisustvo;
    }

    /**
     * Generiši QR kod za prisustvo.
     * 
     * @param string $qrToken
     * @return string SVG QR kod
     */
    public static function generisiQrKod(string $qrToken): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        
        return $writer->writeString($qrToken);
    }

    /**
     * Čekiraj prisustvo skeniranjem QR koda.
     * 
     * @param string $qrToken
     * @param int $edukacijaId
     * @return array Rezultat sa statusom i porukom
     */
    public static function cekirajPrisustvo(string $qrToken, int $edukacijaId): array
    {
        DB::beginTransaction();

        try {
            $prisustvo = Prisustvo::where('qr_token', $qrToken)
                ->where('edukacija_id', $edukacijaId)
                ->first();

            if (!$prisustvo) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Nevažeći QR kod za ovu edukaciju.',
                ];
            }

            if ($prisustvo->prisutan) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Član je već čekiran za ovu edukaciju.',
                    'clan' => $prisustvo->clan->ime . ' ' . $prisustvo->clan->prezime,
                ];
            }

            // Označi kao prisutan
            $prisustvo->update([
                'prisutan' => true,
                'vreme_cekiranja' => now(),
            ]);

            // Dodeli bodove
            $edukacija = $prisustvo->edukacija;
            $bodovi = $edukacija->bodovi;

            if ($bodovi > 0) {
                static::dodeliBodove($prisustvo->clan_id, $edukacija, $bodovi);
                $prisustvo->update(['dodeljeni_bodovi' => $bodovi]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Prisustvo uspešno evidentirano.',
                'clan' => $prisustvo->clan->ime . ' ' . $prisustvo->clan->prezime,
                'bodovi' => $bodovi,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Greška pri čekiranju prisustva', [
                'qr_token' => $qrToken,
                'edukacija_id' => $edukacijaId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Došlo je do greške: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Ručno čekiranje prisustva (bez QR koda).
     * 
     * @param int $edukacijaId
     * @param int $clanId
     * @return array
     */
    public static function rucnoCekiraj(int $edukacijaId, int $clanId): array
    {
        DB::beginTransaction();

        try {
            $prisustvo = Prisustvo::where('edukacija_id', $edukacijaId)
                ->where('clan_id', $clanId)
                ->first();

            if (!$prisustvo) {
                // Automatska prijava ako nije prijavljen
                $prisustvo = static::prijaviClana(
                    Edukacija::findOrFail($edukacijaId), 
                    $clanId
                );
            }

            if ($prisustvo->prisutan) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Član je već čekiran.',
                ];
            }

            $prisustvo->update([
                'prisutan' => true,
                'vreme_cekiranja' => now(),
            ]);

            // Dodeli bodove
            $edukacija = $prisustvo->edukacija;
            $bodovi = $edukacija->bodovi;

            if ($bodovi > 0) {
                static::dodeliBodove($clanId, $edukacija, $bodovi);
                $prisustvo->update(['dodeljeni_bodovi' => $bodovi]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Prisustvo evidentirano.',
                'clan' => $prisustvo->clan->ime . ' ' . $prisustvo->clan->prezime,
                'bodovi' => $bodovi,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Greška: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Dodeli bodove članu za edukaciju.
     * 
     * @param int $clanId
     * @param Edukacija $edukacija
     * @param float $bodovi
     * @return void
     */
    protected static function dodeliBodove(int $clanId, Edukacija $edukacija, float $bodovi): void
    {
        // Računanje licencne godine
        $licencnaGodina = static::izracunajLicencnuGodinu($clanId);

        Bod::create([
            'clan_id' => $clanId,
            'edukacija_id' => $edukacija->id,
            'bodovi' => $bodovi,
            'licencna_godina' => $licencnaGodina,
            'datum' => now(),
            'razlog' => 'Prisustvo na edukaciji: ' . $edukacija->naziv,
            'evidentirao' => auth()->id(),
        ]);
    }

    /**
     * Izračunaj tekuću licencnu godinu za člana.
     * 
     * @param int $clanId
     * @return int
     */
    protected static function izracunajLicencnuGodinu(int $clanId): int
    {
        $clan = \App\Models\Clan::with('licence')->findOrFail($clanId);
        $aktivnaLicenca = $clan->licence->first();

        if (!$aktivnaLicenca || !$aktivnaLicenca->datum_izdavanja) {
            return now()->year;
        }

        $datumIzdavanja = $aktivnaLicenca->datum_izdavanja;
        $licencniPeriod = Podesavanje::get('licencni_period_god', 7);
        
        // Računanje u kojoj licencnoj godini je član
        $godineOdIzdavanja = $datumIzdavanja->diffInYears(now());
        $licencnaGodina = ($godineOdIzdavanja % $licencniPeriod) + 1;

        return now()->year;
    }

    /**
     * Označi edukaciju kao održanu.
     * 
     * @param Edukacija $edukacija
     * @return void
     */
    public static function oznaciKaoOdrzanu(Edukacija $edukacija): void
    {
        $edukacija->update(['status' => 'odrzana']);
    }

    /**
     * Dobij statistike za edukaciju.
     * 
     * @param Edukacija $edukacija
     * @return array
     */
    public static function statistike(Edukacija $edukacija): array
    {
        $prijavljeni = $edukacija->prisustva()->where('prijavljen', true)->count();
        $prisutni = $edukacija->prisustva()->where('prisutan', true)->count();
        $odsutni = $prijavljeni - $prisutni;
        $ukupnoBodova = $edukacija->prisustva()->where('prisutan', true)->sum('dodeljeni_bodovi');

        return [
            'prijavljeni' => $prijavljeni,
            'prisutni' => $prisutni,
            'odsutni' => $odsutni,
            'ukupno_bodova' => $ukupnoBodova,
            'kapacitet' => $edukacija->kapacitet,
            'popunjenost' => $edukacija->kapacitet 
                ? round(($prijavljeni / $edukacija->kapacitet) * 100, 1)
                : null,
        ];
    }
}
