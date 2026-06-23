<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClanarinaPeriod extends Model
{
    protected $table = 'clanarina_periodi';
    protected $fillable = ['naziv', 'vrsta', 'vazi_od', 'vazi_do', 'aktivan'];
    protected $casts = ['vazi_od' => 'date', 'vazi_do' => 'date', 'aktivan' => 'boolean'];

    public function clanarine(): HasMany
    {
        return $this->hasMany(Clanarina::class, 'period_id');
    }
}
