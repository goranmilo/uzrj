<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClanarinaKategorija extends Model
{
    protected $table = 'clanarina_kategorije';
    protected $fillable = ['naziv', 'iznos', 'aktivno'];
    protected $casts = ['aktivno' => 'boolean', 'iznos' => 'decimal:2'];

    public function clanovi(): HasMany
    {
        return $this->hasMany(Clan::class, 'kategorija_clanarine_id');
    }

    public function clanarine(): HasMany
    {
        return $this->hasMany(Clanarina::class, 'kategorija_id');
    }
}
