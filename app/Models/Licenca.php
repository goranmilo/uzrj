<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Licenca extends Model
{
    protected $table = 'licence';
    protected $fillable = ['clan_id', 'broj', 'datum_izdavanja', 'datum_isteka', 'status'];
    protected $casts = ['datum_izdavanja' => 'date', 'datum_isteka' => 'date'];

    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class);
    }

    // Helper: da li ističe za manje od 60 dana
    public function getIsticeAttribute(): bool
    {
        return $this->datum_isteka->diffInDays(now()) <= 60;
    }
}
