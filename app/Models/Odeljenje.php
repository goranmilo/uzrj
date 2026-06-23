<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Odeljenje extends Model
{
    protected $table = 'odeljenja';
    protected $fillable = ['naziv', 'aktivno', 'redosled'];
    protected $casts = ['aktivno' => 'boolean'];

    public function clanovi(): HasMany
    {
        return $this->hasMany(Clan::class, 'odeljenje_id');
    }
}
