<?php

namespace App\Services;

use App\Models\Bod;
use App\Models\Clan;
use App\Models\Licenca;
use App\Models\Podesavanje;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BodoviService
{
    /**
     * Merodavna licenca člana (najskorije izdata).
     */
    public static function licenca(Clan $clan): ?Licenca
    {
        return $clan->licence
            ->filter(fn (Licenca $licenca) => $licenca->datum_izdavanja !== null)
            ->sortByDesc('datum_izdavanja')
            ->first();
    }

    /**
     * Početak tekućeg licencnog perioda (npr. 7-godišnjeg).
     *
     * Licenca izdata 2015. sa periodom od 7 godina je do sada obnovljena,
     * pa tekući period počinje 2022, 2029. itd.
     */
    public static function pocetakLicencnogPerioda(Clan $clan, mixed $referentniDatum = null): ?Carbon
    {
        $licenca = static::licenca($clan);

        if (! $licenca || ! $licenca->datum_izdavanja) {
            return null;
        }

        $licencniPeriod = max(1, (int) Podesavanje::get('licencni_period_god', 7));
        $referentniDatum = filled($referentniDatum) ? Carbon::parse($referentniDatum) : now();

        $pocetak = $licenca->datum_izdavanja->copy()->startOfDay();

        while ($pocetak->copy()->addYears($licencniPeriod)->lte($referentniDatum)) {
            $pocetak->addYears($licencniPeriod);
        }

        return $pocetak;
    }

    /**
     * Početak licencne godine u kojoj se nalazi referentni datum.
     *
     * Licencna godina se računa od datuma izdavanja licence, ne od 1. januara.
     */
    public static function pocetakLicencneGodine(Clan $clan, mixed $referentniDatum = null): ?Carbon
    {
        $pocetakPerioda = static::pocetakLicencnogPerioda($clan, $referentniDatum);

        if (! $pocetakPerioda) {
            return null;
        }

        $referentniDatum = filled($referentniDatum) ? Carbon::parse($referentniDatum) : now();
        $pocetak = $pocetakPerioda->copy();

        while ($pocetak->copy()->addYear()->lte($referentniDatum)) {
            $pocetak->addYear();
        }

        return $pocetak;
    }

    /**
     * Redni broj licencne godine unutar perioda (1 .. licencni_period_god).
     *
     * Vraća null ako član nema licencu.
     */
    public static function redniBrojLicencneGodine(Clan $clan, mixed $referentniDatum = null): ?int
    {
        $pocetakPerioda = static::pocetakLicencnogPerioda($clan, $referentniDatum);
        $pocetakGodine = static::pocetakLicencneGodine($clan, $referentniDatum);

        if (! $pocetakPerioda || ! $pocetakGodine) {
            return null;
        }

        return (int) $pocetakPerioda->diffInYears($pocetakGodine) + 1;
    }

    /**
     * Oznaka licencne godine koja se upisuje u `bodovi.licencna_godina`.
     *
     * To je kalendarska godina u kojoj licencna godina počinje — bodovi se
     * time grupišu po licencnoj, a ne po kalendarskoj godini. Za člana bez
     * licence koristi se kalendarska godina datuma.
     */
    public static function licencnaGodina(Clan $clan, mixed $datum = null): int
    {
        $datum = filled($datum) ? Carbon::parse($datum) : now();

        return static::pocetakLicencneGodine($clan, $datum)?->year ?? $datum->year;
    }

    /**
     * Oznaka licencne godine za člana po ID-u (za forme u administraciji).
     */
    public static function licencnaGodinaZaClan(?int $clanId, mixed $datum = null): int
    {
        $datum = filled($datum) ? Carbon::parse($datum) : now();
        $clan = $clanId ? Clan::with('licence')->find($clanId) : null;

        return $clan ? static::licencnaGodina($clan, $datum) : $datum->year;
    }

    /**
     * Dobij ukupno bodova za člana u tekućem licencnom periodu.
     */
    public static function ukupnoBodovaPeriod(Clan $clan): float
    {
        $pocetakPerioda = static::pocetakLicencnogPerioda($clan);

        if (! $pocetakPerioda) {
            return 0;
        }

        $licencniPeriod = max(1, (int) Podesavanje::get('licencni_period_god', 7));

        return (float) Bod::where('clan_id', $clan->id)
            ->where('datum', '>=', $pocetakPerioda)
            ->where('datum', '<', $pocetakPerioda->copy()->addYears($licencniPeriod))
            ->sum('bodovi');
    }

    /**
     * Dobij bodove za tekuću licencnu godinu.
     *
     * Bodovi se ne prenose iz jedne licencne godine u drugu, pa se sabiraju
     * isključivo unutar tekuće licencne godine.
     */
    public static function bodoviTekucaGodina(Clan $clan): float
    {
        $pocetakGodine = static::pocetakLicencneGodine($clan);

        if (! $pocetakGodine) {
            // Član bez licence — fallback na kalendarsku godinu.
            return (float) Bod::where('clan_id', $clan->id)
                ->whereYear('datum', now()->year)
                ->sum('bodovi');
        }

        return (float) Bod::where('clan_id', $clan->id)
            ->where('datum', '>=', $pocetakGodine)
            ->where('datum', '<', $pocetakGodine->copy()->addYear())
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
        $clan = Clan::with('licence')->findOrFail($clanId);

        return Bod::create([
            'clan_id' => $clanId,
            'edukacija_id' => null,
            'bodovi' => $bodovi,
            'licencna_godina' => static::licencnaGodina($clan),
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
