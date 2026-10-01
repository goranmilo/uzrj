<?php

namespace App\Providers;

use App\Observers\AuditObserver;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Modeli čije se izmene beleže u audit log.
     *
     * @var list<class-string<\Illuminate\Database\Eloquent\Model>>
     */
    public const AUDITOVANI_MODELI = [
        \App\Models\Clan::class,
        \App\Models\Licenca::class,
        \App\Models\Clanarina::class,
        \App\Models\ClanarinaPeriod::class,
        \App\Models\ClanarinaKategorija::class,
        \App\Models\Uplata::class,
        \App\Models\Edukacija::class,
        \App\Models\Prisustvo::class,
        \App\Models\Bod::class,
        \App\Models\Aktuelnost::class,
        \App\Models\Podesavanje::class,
        \App\Models\Sprema::class,
        \App\Models\Zvanje::class,
        \App\Models\Odeljenje::class,
        \App\Models\User::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Jedina prijava je Filament panel. Fortify ostaje samo zbog
        // TwoFactorAuthenticatable traita na User modelu (2FA još nije urađen).
        Fortify::ignoreRoutes();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach (static::AUDITOVANI_MODELI as $model) {
            $model::observe(AuditObserver::class);
        }
    }
}
