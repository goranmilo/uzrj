<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Uplata extends Model
{
    protected $table = 'uplate';
    protected $fillable = ['clanarina_id', 'iznos', 'datum', 'nacin', 'referenca', 'evidentirao'];
    protected $casts = ['datum' => 'date', 'iznos' => 'decimal:2'];

    public function clanarina(): BelongsTo
    {
        return $this->belongsTo(Clanarina::class);
    }

    public function korisnik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evidentirao');
    }
}
