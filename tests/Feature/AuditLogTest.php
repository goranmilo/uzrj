<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Clan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private function clan(): Clan
    {
        return Clan::create([
            'ime' => 'Ana',
            'prezime' => 'Anić',
            'jmbg' => '0101990500011',
            'status' => 'aktivan',
        ]);
    }

    public function test_kreiranje_clana_upisuje_zapis(): void
    {
        $clan = $this->clan();

        $zapis = AuditLog::where('entitet', 'clan')->where('akcija', 'create')->firstOrFail();

        $this->assertSame($clan->id, (int) $zapis->entitet_id);
        $this->assertNull($zapis->pre);
        $this->assertSame('Anić', $zapis->posle['prezime']);
        $this->assertNotNull($zapis->vreme);
    }

    public function test_izmena_belezi_staru_i_novu_vrednost(): void
    {
        $clan = $this->clan();
        $clan->update(['prezime' => 'Perić']);

        $zapis = AuditLog::where('entitet', 'clan')->where('akcija', 'update')->firstOrFail();

        $this->assertSame(['prezime' => 'Anić'], $zapis->pre);
        $this->assertSame(['prezime' => 'Perić'], $zapis->posle);
    }

    public function test_izmena_bez_stvarne_promene_ne_pravi_zapis(): void
    {
        $clan = $this->clan();
        $clan->update(['prezime' => 'Anić']);

        $this->assertSame(0, AuditLog::where('akcija', 'update')->count());
    }

    public function test_brisanje_belezi_zapis(): void
    {
        $clan = $this->clan();
        $id = $clan->id;
        $clan->delete();

        $zapis = AuditLog::where('entitet', 'clan')->where('akcija', 'delete')->firstOrFail();

        $this->assertSame($id, (int) $zapis->entitet_id);
        $this->assertSame('Anić', $zapis->pre['prezime']);
        $this->assertNull($zapis->posle);
    }

    public function test_zapis_pamti_prijavljenog_korisnika(): void
    {
        $korisnik = User::create([
            'name' => 'Operater',
            'email' => 'operater@test.rs',
            'password' => 'tajna-lozinka',
        ]);

        $this->actingAs($korisnik);
        $this->clan();

        $zapis = AuditLog::where('entitet', 'clan')->firstOrFail();

        $this->assertSame($korisnik->id, $zapis->user_id);
    }

    public function test_osetljivi_atributi_se_ne_upisuju(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@test.rs',
            'password' => 'tajna-lozinka',
        ]);

        $zapis = AuditLog::where('entitet', 'user')->firstOrFail();

        $this->assertArrayNotHasKey('password', $zapis->posle);
        $this->assertArrayNotHasKey('remember_token', $zapis->posle);
        $this->assertSame('admin@test.rs', $zapis->posle['email']);
    }
}
