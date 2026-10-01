<?php

namespace Tests\Feature;

use App\Filament\Pages\SystemConfiguration;
use App\Filament\Resources\ClanarinaPeriodResource;
use App\Filament\Resources\ClanarinaPeriodResource\Pages\ListClanarinaPeriods;
use App\Filament\Resources\ClanarinaResource;
use App\Filament\Resources\ClanarinaResource\Pages\ListClanarinas;
use App\Filament\Resources\EdukacijaResource;
use App\Filament\Resources\EdukacijaResource\Pages\ListEdukacijas;
use App\Filament\Resources\UplataResource;
use App\Filament\Resources\UplataResource\Pages\ListUplatas;
use App\Models\Podesavanje;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Izbor kolona za liste edukacija i finansija (isti mehanizam kao za
 * članove — vidi ClanListaKoloneTest).
 */
class ListeKoloneTest extends TestCase
{
    use RefreshDatabase;

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
    }

    /**
     * resurs, lista, kolona van podrazumevanih, podrazumevana kolona (ime kolone u tabeli)
     *
     * @return array<string, array{class-string, class-string, string, string, string}>
     */
    public static function liste(): array
    {
        return [
            'edukacije' => [EdukacijaResource::class, ListEdukacijas::class, 'kapacitet', 'kapacitet', 'naziv'],
            'članarine' => [ClanarinaResource::class, ListClanarinas::class, 'clan_clanski_broj', 'clan.clanski_broj', 'status'],
            'uplate' => [UplataResource::class, ListUplatas::class, 'clan_clanski_broj', 'clanarina.clan.clanski_broj', 'iznos'],
            'periodi' => [ClanarinaPeriodResource::class, ListClanarinaPeriods::class, 'aktivan', 'aktivan', 'naziv'],
        ];
    }

    #[DataProvider('liste')]
    public function test_bez_podesavanja_se_prikazuju_podrazumevane_kolone(string $resurs, string $lista, string $dodatniKljuc, string $dodatnaKolona, string $podrazumevanaKolona): void
    {
        $this->assertSame($resurs::podrazumevaneKolone(), $resurs::izabraneKolone());

        $test = Livewire::test($lista)->assertTableColumnExists($podrazumevanaKolona);

        if (! in_array($dodatniKljuc, $resurs::podrazumevaneKolone(), true)) {
            $test->assertTableColumnDoesNotExist($dodatnaKolona);
        }
    }

    #[DataProvider('liste')]
    public function test_podesavanje_odredjuje_prikazane_kolone(string $resurs, string $lista, string $dodatniKljuc, string $dodatnaKolona, string $podrazumevanaKolona): void
    {
        Podesavanje::set($resurs::kljucPodesavanjaKolona(), [$dodatniKljuc], 'json');

        Livewire::test($lista)
            ->assertTableColumnExists($dodatnaKolona)
            ->assertTableColumnDoesNotExist($podrazumevanaKolona);
    }

    #[DataProvider('liste')]
    public function test_nepoznati_kljuc_u_podesavanju_se_ignorise(string $resurs, string $lista, string $dodatniKljuc): void
    {
        Podesavanje::set($resurs::kljucPodesavanjaKolona(), [$dodatniKljuc, 'nepostojeca_kolona'], 'json');

        $this->assertSame([$dodatniKljuc], $resurs::izabraneKolone());

        Livewire::test($lista)->assertSuccessful();
    }

    public function test_konfiguracija_cuva_izbor_kolona_za_sve_liste(): void
    {
        Livewire::test(SystemConfiguration::class)
            ->fillForm([
                'edukacije_kolone' => ['naziv', 'kapacitet'],
                'clanarine_kolone' => ['clan', 'dug'],
                'uplate_kolone' => ['clan', 'iznos'],
                'clanarina_periodi_kolone' => ['naziv'],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['naziv', 'kapacitet'], Podesavanje::get('edukacije_kolone'));
        $this->assertSame(['clan', 'dug'], Podesavanje::get('clanarine_kolone'));
        $this->assertSame(['clan', 'iznos'], Podesavanje::get('uplate_kolone'));
        $this->assertSame(['naziv'], Podesavanje::get('clanarina_periodi_kolone'));
    }

    public function test_konfiguracija_trazi_bar_jednu_kolonu(): void
    {
        Livewire::test(SystemConfiguration::class)
            ->fillForm(['uplate_kolone' => []])
            ->call('save')
            ->assertHasFormErrors(['uplate_kolone']);
    }
}
