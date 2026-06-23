<?php

namespace App\Services;

use App\Models\Bod;
use App\Models\Clan;
use App\Models\Licenca;
use App\Models\Podesavanje;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BodoviService
{
    /**
     * Dobij ukupno bodova za člana u licencnom periodu.
     * 
     * @param Clan $clan
     * @return float
     */
    public static function ukupnoBodovaPeriod(Clan $clan): float
    {
        $licencniPeriod = Podesavanje::get('licencni_period_god', 7);
        
        // Računanje početka licencnog perioda
        $aktivnaLicenca = $clan->licence->first();
        
        if (!$aktivnaLicenca || !$aktivnaLicenca->datum_izdavanja) {
            return 0;
        }

        $pocetakPerioda = $aktivnaLicenca->datum_izdavanja->copy();
        
        // Ako je prošlo više od licencnog perioda, računaj od poslednjeg obnavljanja
        while ($pocetakPerioda->addYears($licencniPeriod)->isPast()) {
            // Nastavi sledeći period
        }

        return Bod::where('clan_id', $clan->id)
            ->where('datum', '>=', $pocetakPerioda)
            ->sum('bodovi');
    }

    /**
     * Dobij bodove za tekuću licencnu godinu.
     * 
     * @param Clan $clan
     * @return float
     */
    public static function bodoviTekucaGodina(Clan $clan): float
    {
        $aktivnaLicenca = $clan->licence->first();
        
        if (!$aktivnaLicenca || !$aktivnaLicenca->datum_izdavanja) {
            return Bod::where('clan_id', $clan->id)
                ->whereYear('datum', now()->year)
                ->sum('bodovi');
        }

        // Računanje tekuće licencne godine
        $datumIzdavanja = $aktivnaLicenca->datum_izdavanja;
        $licencniPeriod = Podesavanje::get('licencni_period_god', 7);
        
        $godineOdIzdavanja = $datumIzdavanja->diffInYears(now());
        $trenutnaLicencnaGodina = ($godineOdIzdavanja % $licencniPeriod) + 1;
        
        // Početak tekuće licencne godine
        $pocetakGodine = $datumIzdavanja->copy()->addYears($godineOdIzdavanja);
        
        return Bod::where('clan_id', $clan->id)
            ->where('datum', '>=', $pocetakGodine)
            ->sum('bodovi');
    }

    /**
     * Proveri da li član ispunjava godišnji minimum.
     * 
     * @param Clan $clan
     * @return bool
     */
    public static function ispunjavaGodisnjiMinimum(Clan $clan): bool
    {
        $bodovi = static::bodoviTekucaGodina($clan);
        $minimum = Podesavanje::get('godisnji_prag_bodova', 20);
        
        return $bodovi >= $minimum;
    }

    /**
     * Proveri da li član ispunjava ukupan prag za period.
     * 
     * @param Clan $clan
     * @return bool
     */
    public static function ispunjavaUkupanPrag(Clan $clan): bool
    {
        $bodovi = static::ukupnoBodovaPeriod($clan);
        $prag = Podesavanje::get('ukupan_prag_bodova', 140);
        
        return $bodovi >= $prag;
    }

    /**
     * Dobij status napretka člana.
     * 
     * @param Clan $clan
     * @return array
     */
    public static function statusNapretka(Clan $clan): array
    {
        $bodoviGodina = static::bodoviTekucaGodina($clan);
        $bodoviPeriod = static::ukupnoBodovaPeriod($clan);
        
        $godisnjiMinimum = Podesavanje::get('godisnji_prag_bodova', 20);
        $ukupanPrag = Podesavanje::get('ukupan_prag_bodova', 140);
        
        $procenatGodina = $godisnjiMinimum > 0 
            ? min(100, round(($bodoviGodina / $godisnjiMinimum) * 100, 1))
            : 100;
            
        $procenatPeriod = $ukupanPrag > 0 
            ? min(100, round(($bodoviPeriod / $ukupanPrag) * 100, 1))
            : 100;

        return [
            'bodovi_godina' => $bodoviGodina,
            'bodovi_period' => $bodoviPeriod,
            'godisnji_minimum' => $godisnjiMinimum,
            'ukupan_prag' => $ukupanPrag,
            'ispunjava_godisnji' => $bodoviGodina >= $godisnjiMinimum,
            'ispunjava_ukupni' => $bodoviPeriod >= $ukupanPrag,
            'procenat_godina' => $procenatGodina,
            'procenat_period' => $procenatPeriod,
            'preostalo_godina' => max(0, $godisnjiMinimum - $bodoviGodina),
            'preostalo_period' => max(0, $ukupanPrag - $bodoviPeriod),
        ];
    }

    /**
     * Dobij članove koji ne ispunjavaju minimum.
     * 
     * @return Collection
     */
    public static function clanoviIspodMinimuma(): Collection
    {
        $clanovi = Clan::where('status', 'aktivan')
            ->with('licence')
            ->get();

        return $clanovi->filter(function ($clan) {
            return !static::ispunjavaGodisnjiMinimum($clan);
        });
    }

    /**
     * Dobij članove kojima ističe licenca.
     * 
     * @param int $dani
     * @return Collection
     */
    public static function clanoviIsticeLicenca(int $dani = 60): Collection
    {
        $datumGranica = now()->addDays($dani);

        return Licenca::where('status', 'vazeca')
            ->where('datum_isteka', '<=', $datumGranica)
            ->where('datum_isteka', '>=', now())
            ->with('clan')
            ->get();
    }

    /**
     * Ručna korekcija bodova.
     * 
     * @param int $clanId
     * @param float $bodovi
     * @param string $razlog
     * @return Bod
     */
    public static function korekcijaBodova(int $clanId, float $bodovi, string $razlog): Bod
    {
        return Bod::create([
            'clan_id' => $clanId,
            'edukacija_id' => null,
            'bodovi' => $bodovi,
            'licencna_godina' => now()->year,
            'datum' => now(),
            'razlog' => 'Korekcija: ' . $razlog,
            'evidentirao' => auth()->id(),
        ]);
    }

    /**
     * Dobij statistike bodovnog sistema.
     * 
     * @return array
     */
    public static function statistike(): array
    {
        $ukupnoClanova = Clan::where('status', 'aktivan')->count();
        $ispunjavajuMinimum = 0;
        $ispunjavajuUkupni = 0;

        $clanovi = Clan::where('status', 'aktivan')->with('licence')->get();
        
        foreach ($clanovi as $clan) {
            if (static::ispunjavaGodisnjiMinimum($clan)) {
                $ispunjavajuMinimum++;
            }
            if (static::ispunjavaUkupanPrag($clan)) {
                $ispunjavajuUkupni++;
            }
        }

        return [
            'ukupno_clanova' => $ukupnoClanova,
            'ispunjavaju_minimum' => $ispunjavajuMinimum,
            'ispunjavaju_ukupni' => $ispunjavajuUkupni,
            'ne_ispunjavaju_minimum' => $ukupnoClanova - $ispunjavajuMinimum,
            'procenat_minimum' => $ukupnoClanova > 0 
                ? round(($ispunjavajuMinimum / $ukupnoClanova) * 100, 1)
                : 0,
            'procenat_ukupni' => $ukupnoClanova > 0 
                ? round(($ispunjavajuUkupni / $ukupnoClanova) * 100, 1)
                : 0,
        ];
    }
}
