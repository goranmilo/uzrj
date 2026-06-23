<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'audit_log';
    protected $fillable = ['user_id', 'akcija', 'entitet', 'entitet_id', 'pre', 'posle', 'vreme', 'ip_adresa'];
    protected $casts = ['pre' => 'json', 'posle' => 'json', 'vreme' => 'datetime'];

    public function korisnik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
