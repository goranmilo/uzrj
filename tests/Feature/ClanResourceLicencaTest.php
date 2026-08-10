<?php

namespace Tests\Feature;

use App\Filament\Resources\ClanResource\Pages\CreateClan;
use App\Filament\Resources\ClanResource\Pages\EditClan;
use App\Models\Clan;
use App\Models\ClanarinaKategorija;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClanResourceLicencaTest extends TestCase
{
    use RefreshDatabase;

    protected ClanarinaKategorija $kategorija;

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

        $this->kategorija = ClanarinaKategorija::create([
            'naziv' => 'Redovna',
            'iznos' => 1000,
            'aktivno' => true,
        ]);
    }

    public function test_kreiranje_clana_cuva_i_licencu(): void
    {
        Livewire::test(CreateClan::class)
            ->fillForm([
                'ime' => 'Ana',
                'prezime' => 'Anić',
                'jmbg' => '0101990500011',
                'status' => 'aktivan',
                'kategorija_clanarine_id' => $this->kategorija->id,
                'licenca' => [
                    'broj' => 'L-123',
                    'datum_izdavanja' => '2024-05-10',
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $clan = Clan::firstOrFail();

        $this->assertDatabaseHas('licence', [
            'clan_id' => $clan->id,
            'broj' => 'L-123',
        ]);
    }

    public function test_izmena_clana_prikazuje_i_azurira_licencu(): void
    {
        $clan = Clan::create([
            'ime' => 'Ana',
            'prezime' => 'Anić',
            'jmbg' => '0101990500011',
            'status' => 'aktivan',
            'kategorija_clanarine_id' => $this->kategorija->id,
        ]);

        $clan->licence()->create([
            'broj' => 'L-1',
            'datum_izdavanja' => '2024-05-10',
            'datum_isteka' => '2031-05-10',
            'status' => 'vazeca',
        ]);

        Livewire::test(EditClan::class, ['record' => $clan->getRouteKey()])
            ->assertFormSet(['licenca' => [
                'broj' => 'L-1',
                'datum_izdavanja' => '2024-05-10',
                'datum_isteka' => '2031-05-10',
            ]])
            ->fillForm(['licenca' => [
                'broj' => 'L-2',
                'datum_izdavanja' => '2025-01-15',
                'datum_isteka' => null,
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseCount('licence', 1);
        $this->assertDatabaseHas('licence', [
            'clan_id' => $clan->id,
            'broj' => 'L-2',
            'datum_izdavanja' => '2025-01-15',
            'datum_isteka' => '2032-01-15',
        ]);
    }
}
