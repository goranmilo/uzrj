<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Prisustvo extends Model
{
    protected $table = 'prisustva';
    protected $fillable = [
        'edukacija_id', 'clan_id', 'prijavljen', 'prisutan',
        'qr_token', 'qr_poslat_at', 'vreme_cekiranja', 'dodeljeni_bodovi',
    ];
    protected $casts = [
        'prijavljen' => 'boolean',
        'prisutan' => 'boolean',
        'qr_poslat_at' => 'datetime',
        'vreme_cekiranja' => 'datetime',
        'dodeljeni_bodovi' => 'decimal:2',
    ];

    public function edukacija(): BelongsTo
    {
        return $this->belongsTo(Edukacija::class);
    }

    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class);
    }

    // Generiši QR token pri kreiranju
    protected static function booted(): void
    {
        static::creating(function (Prisustvo $prisustvo) {
            if (!$prisustvo->qr_token) {
                $prisustvo->qr_token = Str::uuid();
            }
        });
    }
}
