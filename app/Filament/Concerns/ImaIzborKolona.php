<?php

namespace App\Filament\Concerns;

use App\Models\Podesavanje;

/**
 * Lista resursa čije kolone admin bira u Konfiguraciji sistema.
 *
 * Resurs daje katalog kolona (`dostupneKolone()`), podrazumevani izbor i
 * ključ podešavanja; trait iz podešavanja čita izbor i pravi kolone tabele.
 * Redosled kolona je uvek redosled iz kataloga, ne redosled čekiranja.
 */
trait ImaIzborKolona
{
    /**
     * Kolone koje se mogu prikazati na listi.
     *
     * @return array<string, array{label: string, kolona: \Closure}>
     */
    abstract public static function dostupneKolone(): array;

    /**
     * Kolone koje se prikazuju ako podešavanje nije zadato.
     *
     * @return list<string>
     */
    abstract public static function podrazumevaneKolone(): array;

    /**
     * Ključ u tabeli `podesavanja` pod kojim se čuva izbor (json niz ključeva).
     */
    abstract public static function kljucPodesavanjaKolona(): string;

    /**
     * Izabrane kolone iz podešavanja, u redosledu iz kataloga.
     *
     * @return list<string>
     */
    public static function izabraneKolone(): array
    {
        $izabrane = Podesavanje::get(static::kljucPodesavanjaKolona());

        if (! is_array($izabrane) || $izabrane === []) {
            $izabrane = static::podrazumevaneKolone();
        }

        $poredak = array_keys(static::dostupneKolone());

        return array_values(array_intersect($poredak, $izabrane));
    }

    /**
     * Kolone tabele za izabrane ključeve.
     *
     * @return list<\Filament\Tables\Columns\Column>
     */
    public static function koloneTabele(): array
    {
        $katalog = static::dostupneKolone();

        return array_map(
            fn (string $kljuc) => ($katalog[$kljuc]['kolona'])(),
            static::izabraneKolone(),
        );
    }
}
