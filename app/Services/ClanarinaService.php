<?php

namespace App\Services;

use App\Models\Clan;
use App\Models\Clanarina;
use App\Models\ClanarinaKategorija;
use App\Models\ClanarinaPeriod;
use App\Models\Podesavanje;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClanarinaService
{
    /**
     * Zaduži sve aktivne članove za dati period.
     * 
     * @return int Broj kreiranih zaduženja
     */
    public static function zaduziSveClanove(ClanarinaPeriod $period): int
    {
        $clanovi = Clan::where('status', 'aktivan')
            ->whereNotNull('kategorija_clanarine_id')
            ->get();

        $count = 0;

        foreach ($clanovi as $clan) {
            try {
                static::zaduziClana($clan, $period);
                $count++;
            } catch (\Exception $e) {
                Log::error('Greška pri zaduženju člana', [
                    'clan_id' => $clan->id,
                    'period_id' => $period->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    /**
     * Zaduži jednog člana za dati period.
     * 
     * @return Clanarina Kreirano zaduženje
     */
    public static function zaduziClana(Clan $clan, ClanarinaPeriod $period): Clanarina
    {
        // Provera da li već postoji zaduženje
        $postoji = Clanarina::where('clan_id', $clan->id)
            ->where('period_id', $period->id)
            ->exists();

        if ($postoji) {
            throw new \Exception('Član već ima zaduženje za ovaj period.');
        }

        // Dobijanje kategorije i iznosa
        $kategorija = $clan->kategorijaClanarine;
        
        if (!$kategorija) {
            throw new \Exception('Član nema definisanu kategoriju članarine.');
        }

        $osnovniIznos = $kategorija->iznos;

        // Pro-rata obračun ako je član učlanjen usred perioda
        $iznos = static::izracunajProRata($clan, $period, $osnovniIznos);

        // Kreiranje zaduženja
        return Clanarina::create([
            'clan_id' => $clan->id,
            'period_id' => $period->id,
            'kategorija_id' => $kategorija->id,
            'iznos_zaduzenja' => $iznos,
            'iznos_placen' => 0,
            'status' => 'dug',
        ]);
    }

    /**
     * Izračunaj pro-rata iznos ako se član učlanio usred perioda.
     * 
     * @param Clan $clan
     * @param ClanarinaPeriod $period
     * @param float $osnovniIznos
     * @return float
     */
    protected static function izracunajProRata(Clan $clan, ClanarinaPeriod $period, float $osnovniIznos): float
    {
        // Provera da li je pro-rata uključen
        $proRataEnabled = Podesavanje::get('pro_rata_racunanje', true);

        if (!$proRataEnabled) {
            return $osnovniIznos;
        }

        // Ako član nema datum učlanjenja, vraćamo pun iznos
        if (!$clan->datum_uclanjenja) {
            return $osnovniIznos;
        }

        $datumUclanjenja = Carbon::parse($clan->datum_uclanjenja);
        $periodOd = Carbon::parse($period->vazi_od);
        $periodDo = Carbon::parse($period->vazi_do);

        // Ako se član učlanio pre početka perioda, pun iznos
        if ($datumUclanjenja->lte($periodOd)) {
            return $osnovniIznos;
        }

        // Ako se član učlanio posle kraja perioda, nema zaduženja
        if ($datumUclanjenja->gte($periodDo)) {
            return 0;
        }

        // Računanje pro-rata
        $ukupnoDana = $periodOd->diffInDays($periodDo);
        $preostaloDana = $datumUclanjenja->diffInDays($periodDo);

        if ($ukupnoDana <= 0) {
            return $osnovniIznos;
        }

        $procenat = $preostaloDana / $ukupnoDana;
        $iznos = round($osnovniIznos * $procenat, 2);

        return $iznos;
    }

    /**
     * Evidentiraj uplatu za članarinu.
     * 
     * @param Clanarina $clanarina
     * @param float $iznos
     * @param string $nacin
     * @param string|null $referenca
     * @return void
     */
    public static function evidentirajUplatu(
        Clanarina $clanarina, 
        float $iznos, 
        string $nacin = 'gotovina', 
        ?string $referenca = null
    ): void {
        DB::beginTransaction();

        try {
            // Kreiranje uplate
            $clanarina->uplate()->create([
                'iznos' => $iznos,
                'datum' => now(),
                'nacin' => $nacin,
                'referenca' => $referenca,
                'evidentirao' => auth()->id(),
            ]);

            // Ažuriranje ukupnog iznosa plaćenog
            $ukupnoPlaceno = $clanarina->uplate()->sum('iznos');
            $clanarina->update([
                'iznos_placen' => $ukupnoPlaceno,
            ]);

            // Ažuriranje statusa
            static::azurirajStatus($clanarina);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Ažuriraj status članarine na osnovu uplata.
     * 
     * @param Clanarina $clanarina
     * @return void
     */
    protected static function azurirajStatus(Clanarina $clanarina): void
    {
        $dug = $clanarina->iznos_zaduzenja - $clanarina->iznos_placen;

        if ($dug <= 0) {
            $status = 'placeno';
        } elseif ($clanarina->iznos_placen > 0) {
            $status = 'delimicno';
        } else {
            $status = 'dug';
        }

        $clanarina->update(['status' => $status]);
    }

    /**
     * Dobij ukupno dugovanje za člana.
     * 
     * @param Clan $clan
     * @return float
     */
    public static function ukupnoDugovanje(Clan $clan): float
    {
        return Clanarina::where('clan_id', $clan->id)
            ->where('status', '!=', 'placeno')
            ->sum(DB::raw('iznos_zaduzenja - iznos_placen'));
    }

    /**
     * Dobij pregled dugovanja po članovima.
     * 
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public static function topDuznici(int $limit = 10): \Illuminate\Support\Collection
    {
        return Clan::select('clanovi.*')
            ->selectRaw('SUM(clanarine.iznos_zaduzenja - clanarine.iznos_placen) as ukupan_dug')
            ->join('clanarine', 'clanovi.id', '=', 'clanarine.clan_id')
            ->where('clanarine.status', '!=', 'placeno')
            ->groupBy('clanovi.id')
            ->orderByDesc('ukupan_dug')
            ->limit($limit)
            ->get();
    }

    /**
     * Automatski ažuriraj statuse svih članarina.
     * 
     * @return void
     */
    public static function azurirajSveStatuse(): void
    {
        $clanarine = Clanarina::where('status', '!=', 'placeno')->get();

        foreach ($clanarine as $clanarina) {
            static::azurirajStatus($clanarina);
        }
    }
}
