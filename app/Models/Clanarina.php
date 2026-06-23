<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clanarina extends Model
{
    protected $table = 'clanarine';
    protected $fillable = ['clan_id', 'period_id', 'kategorija_id', 'iznos_zaduzenja', 'iznos_placen', 'status'];
    protected $casts = ['iznos_zaduzenja' => 'decimal:2', 'iznos_placen' => 'decimal:2'];

    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(ClanarinaPeriod::class, 'period_id');
    }

    public function kategorija(): BelongsTo
    {
        return $this->belongsTo(ClanarinaKategorija::class, 'kategorija_id');
    }

    public function uplate(): HasMany
    {
        return $this->hasMany(Uplata::class, 'clanarina_id');
    }

    // Helper: preostali dug
    public function getDugAttribute(): float
    {
        return (float) ($this->iznos_zaduzenja - $this->iznos_placen);
    }
}
