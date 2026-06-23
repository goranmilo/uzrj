<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Provera da li korisnik može pristupiti Filament panelu.
     * Samo admin i operater imaju pristup.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole(['admin', 'operater']);
    }

    /**
     * Da li korisnik ima 2FA omogućen.
     */
    public function has2FAEnabled(): bool
    {
        return !is_null($this->two_factor_secret);
    }

    /**
     * Da li je admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Da li je operater.
     */
    public function isOperater(): bool
    {
        return $this->hasRole('operater');
    }

    /**
     * Da li može pristupiti konfiguraciji sistema.
     */
    public function canConfigureSystem(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Relacija ka audit log-u.
     */
    public function auditLog()
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }
}
