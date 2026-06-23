<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sprema extends Model
{
    protected $table = 'spreme';
    protected $fillable = ['naziv', 'aktivno', 'redosled'];
    protected $casts = ['aktivno' => 'boolean'];

    public function clanovi(): HasMany
    {
        return $this->hasMany(Clan::class, 'sprema_id');
    }
}
