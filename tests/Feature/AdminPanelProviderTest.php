<?php

namespace Tests\Feature;

use App\Models\Podesavanje;
use App\Providers\Filament\AdminPanelProvider;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Filament pored naše stranice "Tema aplikacije" nudi i svoj ugrađeni
 * prekidač svetla/tamna/sistemski (pored avatara). Taj prekidač pamti izbor
 * po pregledaču (localStorage) i od tog trenutka trajno ignoriše podešavanje
 * iz baze za taj pregledač. Panel zato mora da forsira mod preko darkMode()
 * — ne defaultThemeMode(), koji je samo fallback za pregledače bez sačuvanog
 * izbora i ne rešava problem za pregledače koji su već nešto izabrali.
 */
class AdminPanelProviderTest extends TestCase
{
    use RefreshDatabase;

    private function registrovanPanel(): Panel
    {
        return (new AdminPanelProvider($this->app))->panel(Panel::make());
    }

    public function test_tamni_rezim_iskljucen_forsira_svetlu_temu_bez_prekidaca(): void
    {
        Podesavanje::set('tema_dark_mode', false, 'boolean');

        $panel = $this->registrovanPanel();

        $this->assertFalse($panel->hasDarkMode());
        $this->assertFalse($panel->hasDarkModeForced());
    }

    public function test_tamni_rezim_ukljucen_forsira_tamnu_temu_bez_prekidaca(): void
    {
        Podesavanje::set('tema_dark_mode', true, 'boolean');

        $panel = $this->registrovanPanel();

        $this->assertTrue($panel->hasDarkMode());
        $this->assertTrue($panel->hasDarkModeForced());
    }
}
