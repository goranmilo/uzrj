<?php

namespace Tests\Feature;

use App\Filament\Pages\BodoviPregled;
use App\Filament\Pages\SystemConfiguration;
use App\Filament\Pages\ThemeSettings;
use App\Filament\Resources\AuditLogResource;
use App\Filament\Resources\BodResource;
use App\Filament\Resources\ClanResource;
use App\Models\Clan;
use App\Models\Podesavanje;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PanelSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected Clan $clan;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin', 'web');

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.rs',
            'password' => 'tajna-lozinka',
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->clan = Clan::create([
            'ime' => 'Ana',
            'prezime' => 'Anić',
            'jmbg' => '0101990500011',
            'status' => 'aktivan',
            'datum_uclanjenja' => '2024-01-15',
        ]);

        $this->clan->licence()->create([
            'broj' => 'L-1',
            'datum_izdavanja' => '2024-05-10',
            'datum_isteka' => '2031-05-10',
            'status' => 'vazeca',
        ]);
    }

    public function test_glavne_stranice_panela_se_otvaraju(): void
    {
        $this->get(Dashboard::getUrl())->assertOk();
        $this->get(ClanResource::getUrl('index'))->assertOk();
        $this->get(ClanResource::getUrl('edit', ['record' => $this->clan]))->assertOk();
        $this->get(ClanResource::getUrl('view', ['record' => $this->clan]))->assertOk();
        $this->get(BodResource::getUrl('index'))->assertOk();
        $this->get(BodResource::getUrl('create'))->assertOk();
        $this->get(AuditLogResource::getUrl('index'))->assertOk();
        $this->get(BodoviPregled::getUrl())->assertOk();
        $this->get(SystemConfiguration::getUrl())->assertOk();
        $this->get(ThemeSettings::getUrl())->assertOk();
    }

    public function test_konfiguracija_sistema_cuva_izmene(): void
    {
        Livewire::test(SystemConfiguration::class)
            ->fillForm(['godisnji_prag_bodova' => 25])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(25, Podesavanje::get('godisnji_prag_bodova'));
    }

    public function test_stranica_panela_sadrzi_css_varijable_teme(): void
    {
        $this->get(Dashboard::getUrl())
            ->assertOk()
            ->assertSee('--theme-primary', escape: false);
    }
}
