<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Jmbg implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->isValidJmbg($value)) {
            $fail('JMBG nije ispravan.');
        }
    }

    /**
     * Proverava da li je JMBG validan.
     * 
     * JMBG (Jedinstveni matični broj građana) se sastoji od 13 cifara:
     * - 7 cifara: datum rođenja (DD MM GGG)
     * - 3 cifre: regionalni kod
     * - 2 cifre: serijski broj
     * - 1 cifra: kontrolna cifra
     */
    private function isValidJmbg(string $jmbg): bool
    {
        // Mora imati tačno 13 cifara
        if (!preg_match('/^\d{13}$/', $jmbg)) {
            return false;
        }

        // Izdvajanje delova
        $day = (int) substr($jmbg, 0, 2);
        $month = (int) substr($jmbg, 2, 2);
        $year = (int) substr($jmbg, 4, 3);
        $region = (int) substr($jmbg, 7, 3);
        $serial = (int) substr($jmbg, 10, 2);
        $control = (int) substr($jmbg, 12, 1);

        // Određivanje veka
        // Ako je godina 0-99, pretpostavljamo 2000-te (za mlađe od 25 godina)
        // Inače 1900-te
        $fullYear = $year < 100 ? 2000 + $year : 1900 + $year;

        // Validacija datuma
        if (!checkdate($month, $day, $fullYear)) {
            return false;
        }

        // Računanje kontrolne cifre
        // Formula: 11 - ((7*(a1+a7) + 6*(a2+a8) + 5*(a3+a9) + 4*(a4+a10) + 3*(a5+a11) + 2*(a6+a12)) mod 11)
        $a = array_map('intval', str_split($jmbg));
        
        $sum = 7 * ($a[0] + $a[6]) +
               6 * ($a[1] + $a[7]) +
               5 * ($a[2] + $a[8]) +
               4 * ($a[3] + $a[9]) +
               3 * ($a[4] + $a[10]) +
               2 * ($a[5] + $a[11]);

        $remainder = $sum % 11;
        $calculatedControl = 11 - $remainder;

        // Ako je rezultat 10, 11 ili 12, kontrolna cifra je 0
        if ($calculatedControl >= 10) {
            $calculatedControl = 0;
        }

        return $control === $calculatedControl;
    }
}
