<?php

namespace App\Services;

use App\Mail\MesecniIzvestaj;
use App\Models\Aktuelnost;
use App\Models\Clan;
use App\Models\MailIzvestaj;
use App\Models\Podesavanje;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Pošalji mesečni izveštaj svim aktivnim članovima.
     * 
     * @return int Broj poslatih mejlova
     */
    public static function posaljiMesecniIzvestaj(): int
    {
        $clanovi = Clan::where('status', 'aktivan')
            ->whereNotNull('email')
            ->get();

        $poslato = 0;

        foreach ($clanovi as $clan) {
            try {
                static::posaljiIzvestajZaClana($clan);
                $poslato++;
            } catch (\Exception $e) {
                Log::error('Greška pri slanju mesečnog izveštaja', [
                    'clan_id' => $clan->id,
                    'email' => $clan->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $poslato;
    }

    /**
     * Pošalji izveštaj za jednog člana.
     * 
     * @param Clan $clan
     * @return void
     */
    public static function posaljiIzvestajZaClana(Clan $clan): void
    {
        if (empty($clan->email)) {
            return;
        }

        // Priprema podataka
        $data = static::pripremiPodatke($clan);

        // Slanje mejla
        Mail::to($clan->email)->send(new MesecniIzvestaj($clan, $data));

        // Logovanje
        MailIzvestaj::create([
            'clan_id' => $clan->id,
            'tip' => 'mesecni_izvestaj',
            'poslat_at' => now(),
            'status' => 'poslat',
        ]);
    }

    /**
     * Pripremi podatke za izveštaj.
     * 
     * @param Clan $clan
     * @return array
     */
    protected static function pripremiPodatke(Clan $clan): array
    {
        // Aktuelnosti
        $aktuelnosti = Aktuelnost::where('objavljeno', true)
            ->orderBy('datum_objave', 'desc')
            ->limit(5)
            ->get();

        // Status članarine
        $clanarina = \App\Models\Clanarina::where('clan_id', $clan->id)
            ->where('status', '!=', 'placeno')
            ->with('period')
            ->first();

        // Predstojeće edukacije
        $edukacije = \App\Models\Edukacija::where('status', 'planirana')
            ->where('datum_pocetka', '>=', now())
            ->whereHas('prisustva', function ($query) use ($clan) {
                $query->where('clan_id', $clan->id)
                    ->where('prijavljen', true);
            })
            ->orderBy('datum_pocetka')
            ->limit(5)
            ->get();

        // Poslednje edukacije sa bodovima
        $bodovi = \App\Models\Bod::where('clan_id', $clan->id)
            ->with('edukacija')
            ->orderBy('datum', 'desc')
            ->limit(5)
            ->get();

        // Status bodova
        $statusBodova = BodoviService::statusNapretka($clan);

        return [
            'aktuelnosti' => $aktuelnosti,
            'clanarina' => $clanarina,
            'edukacije' => $edukacije,
            'bodovi' => $bodovi,
            'statusBodova' => $statusBodova,
        ];
    }

    /**
     * Pošalji podsetnik za neplaćenu članarinu.
     * 
     * @return int
     */
    public static function posaljiPodsetnikClanarine(): int
    {
        $clanarine = \App\Models\Clanarina::where('status', '!=', 'placeno')
            ->whereHas('clan', function ($query) {
                $query->where('status', 'aktivan')
                    ->whereNotNull('email');
            })
            ->with('clan', 'period')
            ->get();

        $poslato = 0;

        foreach ($clanarine as $clanarina) {
            try {
                // TODO: Kreirati poseban Mail za podsetnik
                
                MailIzvestaj::create([
                    'clan_id' => $clanarina->clan_id,
                    'tip' => 'podsetnik_clanarine',
                    'poslat_at' => now(),
                    'status' => 'poslat',
                ]);
                
                $poslato++;
            } catch (\Exception $e) {
                Log::error('Greška pri slanju podsetnika', [
                    'clan_id' => $clanarina->clan_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $poslato;
    }

    /**
     * Pošalji podsetnik za predstojeću edukaciju.
     * 
     * @param int $edukacijaId
     * @return int
     */
    public static function posaljiPodsetnikEdukacije(int $edukacijaId): int
    {
        $edukacija = \App\Models\Edukacija::findOrFail($edukacijaId);

        $prisustva = $edukacija->prisustva()
            ->where('prijavljen', true)
            ->whereHas('clan', function ($query) {
                $query->where('status', 'aktivan')
                    ->whereNotNull('email');
            })
            ->with('clan')
            ->get();

        $poslato = 0;

        foreach ($prisustva as $prisustvo) {
            try {
                // TODO: Kreirati poseban Mail za podsetnik edukacije
                
                MailIzvestaj::create([
                    'clan_id' => $prisustvo->clan_id,
                    'tip' => 'podsetnik_edukacija',
                    'poslat_at' => now(),
                    'status' => 'poslat',
                ]);
                
                $poslato++;
            } catch (\Exception $e) {
                Log::error('Greška pri slanju podsetnika edukacije', [
                    'clan_id' => $prisustvo->clan_id,
                    'edukacija_id' => $edukacijaId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $poslato;
    }

    /**
     * Pošalji upozorenje za članove ispod minimuma bodova.
     * 
     * @return int
     */
    public static function posaljiUpozorenjeBodovi(): int
    {
        $clanoviIspod = BodoviService::clanoviIspodMinimuma()
            ->filter(fn ($clan) => !empty($clan->email));

        $poslato = 0;

        foreach ($clanoviIspod as $clan) {
            try {
                // TODO: Kreirati poseban Mail za upozorenje
                
                MailIzvestaj::create([
                    'clan_id' => $clan->id,
                    'tip' => 'upozorenje_bodovi',
                    'poslat_at' => now(),
                    'status' => 'poslat',
                ]);
                
                $poslato++;
            } catch (\Exception $e) {
                Log::error('Greška pri slanju upozorenja', [
                    'clan_id' => $clan->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $poslato;
    }
}
