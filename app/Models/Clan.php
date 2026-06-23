<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Clan extends Model
{
    protected $table = 'clanovi';
    protected $fillable = [
        'ime', 'prezime', 'jmbg', 'okg', 'email', 'telefon',
        'sprema_id', 'zvanje_id', 'odeljenje_id', 'kategorija_clanarine_id',
        'status', 'datum_uclanjenja', 'clanski_broj',
    ];

    public function sprem(): BelongsTo
    {
        return $this->belongsTo(Sprema::class, 'sprema_id');
    }

    public function zvanje(): BelongsTo
    {
        return $this->belongsTo(Zvanje::class, 'zvanje_id');
    }

    public function odeljenje(): BelongsTo
    {
        return $this->belongsTo(Odeljenje::class, 'odeljenje_id');
    }

    public function kategorijaClanarine(): BelongsTo
    {
        return $this->belongsTo(ClanarinaKategorija::class, 'kategorija_clanarine_id');
    }

    public function licence(): HasMany
    {
        return $this->hasMany(Licenca::class);
    }

    public function aktivnaLicenca(): HasOne
    {
        return $this->hasOne(Licenca::class)->where('status', 'vazeca')->latest();
    }

    public function clanarine(): HasMany
    {
        return $this->hasMany(Clanarina::class);
    }

    public function prisustva(): HasMany
    {
        return $this->hasMany(Prisustvo::class);
    }

    public function bodovi(): HasMany
    {
        return $this->hasMany(Bod::class);
    }

    public function mailIzvestaji(): HasMany
    {
        return $this->hasMany(MailIzvestaj::class);
    }

    // Helper: puno ime
    public function getImePunoAttribute(): string
    {
        return $this->ime . ' ' . $this->prezime;
    }

    // Helper: ukupno bodova
    public function getUkupnoBodovaAttribute(): float
    {
        return (float) $this->bodovi()->sum('bodovi');
    }

    // Helper: bodovi u tekućoj licencnoj godini
    public function getBodoviTekucaGodinaAttribute(): float
    {
        // TODO: računanje tekuće licencne godine na osnovu datuma licence
        return (float) $this->bodovi()->whereYear('datum', now()->year)->sum('bodovi');
    }
}
