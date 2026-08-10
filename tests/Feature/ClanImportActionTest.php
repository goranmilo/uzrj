<?php

namespace Tests\Feature;

use App\Filament\Resources\ClanResource\Pages\ListClans;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Uvoz članova kroz akciju u panelu — ranije je padao sa
 * FileNotFoundException jer je Excel-u prosleđivano ime fajla umesto putanje.
 */
class ClanImportActionTest extends TestCase
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

    private function posalji(string $sadrzaj): Testable
    {
        return Livewire::test(ListClans::class)
            ->mountAction('import')
            ->set(
                'mountedActionsData.0.file',
                UploadedFile::fake()->createWithContent('clanovi.csv', $sadrzaj),
            )
            ->callMountedAction();
    }

    public function test_akcija_uvoza_ucitava_poslati_fajl(): void
    {
        $this->posalji(<<<'CSV'
        ime,prezime,jmbg,email
        Ana,Anić,0101990500011,ana@test.rs
        Marko,Marković,1503857800120,marko@test.rs
        CSV)->assertHasNoActionErrors();

        $this->assertDatabaseCount('clanovi', 2);
        $this->assertDatabaseHas('clanovi', ['jmbg' => '1503857800120']);
    }

    public function test_neispravan_fajl_prijavljuje_gresku_umesto_pada(): void
    {
        $this->posalji('ovo nije tabela')->assertHasNoActionErrors();

        $this->assertDatabaseCount('clanovi', 0);
    }
}
