<?php

namespace App\Services;

use App\Models\Clan;
use App\Models\Licenca;
use App\Models\Podesavanje;
use Carbon\Carbon;

class LicencaService
{
    /**
     * Sačuvaj podatke o licenci unete kroz formu člana.
     *
     * Ažurira merodavnu (najskorije izdatu) licencu ili kreira novu.
     * Vraća null ako u formi nije unet nijedan podatak o licenci.
     *
     * @param  array{broj?: string|null, datum_izdavanja?: mixed, datum_isteka?: mixed}  $podaci
     */
    public static function sacuvaj(Clan $clan, array $podaci): ?Licenca
    {
        $broj = trim((string) ($podaci['broj'] ?? ''));
        $datumIzdavanja = $podaci['datum_izdavanja'] ?? null;
        $datumIsteka = $podaci['datum_isteka'] ?? null;

        if ($broj === '' && blank($datumIzdavanja) && blank($datumIsteka)) {
            return null;
        }

        $licenca = BodoviService::licenca($clan) ?? $clan->licence()->latest('id')->first();

        // Bez datuma izdavanja ne može se izračunati licencna godina — ako je
        // licenca već upisana, zadržava se postojeći datum.
        $datumIzdavanja = filled($datumIzdavanja)
            ? Carbon::parse($datumIzdavanja)->startOfDay()
            : $licenca?->datum_izdavanja?->copy();

        if (! $datumIzdavanja) {
            return null;
        }

        $datumIsteka = filled($datumIsteka)
            ? Carbon::parse($datumIsteka)->startOfDay()
            : static::podrazumevaniDatumIsteka($datumIzdavanja);

        $atributi = [
            'broj' => $broj !== '' ? $broj : ($licenca->broj ?? '—'),
            'datum_izdavanja' => $datumIzdavanja,
            'datum_isteka' => $datumIsteka,
            'status' => static::status($datumIsteka),
        ];

        if ($licenca) {
            $licenca->update($atributi);

            return $licenca;
        }

        return $clan->licence()->create($atributi);
    }

    /**
     * Podaci merodavne licence za popunjavanje forme člana.
     *
     * @return array{broj: string|null, datum_izdavanja: string|null, datum_isteka: string|null}
     */
    public static function podaciZaFormu(Clan $clan): array
    {
        $licenca = BodoviService::licenca($clan) ?? $clan->licence()->latest('id')->first();

        return [
            'broj' => $licenca?->broj,
            'datum_izdavanja' => $licenca?->datum_izdavanja?->toDateString(),
            'datum_isteka' => $licenca?->datum_isteka?->toDateString(),
        ];
    }

    /**
     * Istek licence = datum izdavanja + licencni period iz podešavanja.
     */
    public static function podrazumevaniDatumIsteka(Carbon $datumIzdavanja): Carbon
    {
        $licencniPeriod = max(1, (int) Podesavanje::get('licencni_period_god', 7));

        return $datumIzdavanja->copy()->addYears($licencniPeriod);
    }

    /**
     * Status licence prema datumu isteka i pragu upozorenja iz podešavanja.
     */
    public static function status(Carbon $datumIsteka): string
    {
        $daniUpozorenje = max(0, (int) Podesavanje::get('dani_pre_isteka_upozorenje', 60));

        if ($datumIsteka->isPast()) {
            return 'istekla';
        }

        if ($datumIsteka->lte(now()->addDays($daniUpozorenje))) {
            return 'istice';
        }

        return 'vazeca';
    }

    /**
     * Osveži statuse svih licenci (npr. iz zakazane komande).
     *
     * @return int Broj promenjenih licenci
     */
    public static function osveziStatuse(): int
    {
        $promenjeno = 0;

        Licenca::query()->each(function (Licenca $licenca) use (&$promenjeno) {
            if (! $licenca->datum_isteka) {
                return;
            }

            $status = static::status($licenca->datum_isteka);

            if ($licenca->status !== $status) {
                $licenca->update(['status' => $status]);
                $promenjeno++;
            }
        });

        return $promenjeno;
    }
}
