<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aktuelnost extends Model
{
    protected $table = 'aktuelnosti';
    protected $fillable = ['naslov', 'sadrzaj', 'datum_objave', 'objavljeno'];
    protected $casts = ['datum_objave' => 'date', 'objavljeno' => 'boolean'];
}
