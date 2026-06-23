<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Edukacija extends Model
{
    protected $table = 'edukacije';
    protected $fillable = [
        'naziv', 'opis', 'datum_pocetka', 'datum_zavrsetka', 'lokacija',
        'kapacitet', 'predavaci', 'akreditacioni_broj', 'vrsta_kme',
        'bodovi', 'ciljna_grupa', 'status',
    ];
    protected $casts = [
        'datum_pocetka' => 'datetime',
        'datum_zavrsetka' => 'datetime',
        'bodovi' => 'decimal:2',
    ];

    public function prisustva(): HasMany
    {
        return $this->hasMany(Prisustvo::class);
    }

    // Helper: broj prijavljenih
    public function getPrijavljeniCountAttribute(): int
    {
        return $this->prisustva()->where('prijavljen', true)->count();
    }

    // Helper: broj prisutnih
    public function getPrisutniCountAttribute(): int
    {
        return $this->prisustva()->where('prisutan', true)->count();
    }

    // Helper: da li je popunjena
    public function getPopunjenaAttribute(): bool
    {
        if (!$this->kapacitet) return false;
        return $this->prijavljeni_count >= $this->kapacitet;
    }
}
