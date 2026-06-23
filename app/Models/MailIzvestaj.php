<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailIzvestaj extends Model
{
    protected $table = 'mail_izvestaji';
    protected $fillable = ['clan_id', 'tip', 'poslat_at', 'status', 'greska'];
    protected $casts = ['poslat_at' => 'datetime'];

    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class);
    }
}
