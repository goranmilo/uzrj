<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zvanje extends Model
{
    protected $table = 'zvanja';
    protected $fillable = ['naziv', 'aktivno', 'redosled'];
    protected $casts = ['aktivno' => 'boolean'];

    public function clanovi(): HasMany
    {
        return $this->hasMany(Clan::class, 'zvanje_id');
    }
}
