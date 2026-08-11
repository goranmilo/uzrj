<?php

namespace Tests\Feature;

use App\Filament\Pages\SystemConfiguration;
use App\Filament\Resources\ClanResource;
use App\Filament\Resources\ClanResource\Pages\EditClan;
use App\Filament\Resources\ClanResource\Pages\ListClans;
use App\Models\Clan;
use App\Models\Podesavanje;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClanListaKoloneTest extends TestCase
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
            'email' => 'ana@test.rs',
            'status' => 'aktivan',
        ]);
    }

    public function test_bez_podesavanja_se_prikazuju_podrazumevane_kolone(): void
    {
        $this->assertSame(ClanResource::podrazumevaneKolone(), ClanResource::izabraneKolone());

        Livewire::test(ListClans::class)
            ->assertTableColumnExists('ime')
            ->assertTableColumnExists('status')
            ->assertTableColumnDoesNotExist('email');
    }

    public function test_podesavanje_odredjuje_prikazane_kolone(): void
    {
        Podesavanje::set('clanovi_kolone', ['ime', 'prezime', 'email'], 'json');

        Livewire::test(ListClans::class)
            ->assertTableColumnExists('ime')
            ->assertTableColumnExists('email')
            ->assertTableColumnDoesNotExist('status')
            ->assertTableColumnDoesNotExist('jmbg');
    }

    public function test_nepoznati_kljuc_u_podesavanju_se_ignorise(): void
    {
        Podesavanje::set('clanovi_kolone', ['ime', 'nepostojeca_kolona'], 'json');

        $this->assertSame(['ime'], ClanResource::izabraneKolone());

        Livewire::test(ListClans::class)->assertSuccessful();
    }

    public function test_konfiguracija_cuva_izbor_kolona(): void
    {
        Livewire::test(SystemConfiguration::class)
            ->fillForm(['clanovi_kolone' => ['ime', 'prezime', 'telefon']])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['ime', 'prezime', 'telefon'], Podesavanje::get('clanovi_kolone'));
    }

    public function test_lista_clanova_nema_akciju_brisanja(): void
    {
        Livewire::test(ListClans::class)
            ->assertTableActionExists('edit')
            ->assertTableActionDoesNotExist('delete')
            ->assertTableBulkActionDoesNotExist('delete');
    }

    public function test_brisanje_ostaje_na_stranici_izmene_clana(): void
    {
        Livewire::test(EditClan::class, ['record' => $this->clan->getRouteKey()])
            ->assertActionExists('delete');
    }
}
