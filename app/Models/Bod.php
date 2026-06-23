<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bod extends Model
{
    protected $table = 'bodovi';
    protected $fillable = ['clan_id', 'edukacija_id', 'bodovi', 'licencna_godina', 'datum', 'razlog', 'evidentirao'];
    protected $casts = ['datum' => 'date', 'bodovi' => 'decimal:2'];

    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class);
    }

    public function edukacija(): BelongsTo
    {
        return $this->belongsTo(Edukacija::class);
    }

    public function korisnik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evidentirao');
    }
}
