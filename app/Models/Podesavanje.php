<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Podesavanje extends Model
{
    protected $table = 'podesavanja';
    protected $fillable = ['kljuc', 'vrednost', 'tip'];
    
    // Dohvati vrednost po ključu
    public static function get(string $kljuc, $default = null)
    {
        $podesavanje = static::where('kljuc', $kljuc)->first();
        if (!$podesavanje) return $default;
        
        return match($podesavanje->tip) {
            'integer' => (int) $podesavanje->vrednost,
            'boolean' => (bool) $podesavanje->vrednost,
            'json' => json_decode($podesavanje->vrednost, true),
            default => $podesavanje->vrednost,
        };
    }

    // Postavi vrednost po ključu
    public static function set(string $kljuc, $vrednost, string $tip = 'string'): void
    {
        $vrednostStr = match($tip) {
            'json' => json_encode($vrednost),
            'boolean' => $vrednost ? '1' : '0',
            default => (string) $vrednost,
        };

        static::updateOrCreate(
            ['kljuc' => $kljuc],
            ['vrednost' => $vrednostStr, 'tip' => $tip]
        );
    }
}
