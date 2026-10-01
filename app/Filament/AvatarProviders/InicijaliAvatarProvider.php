<?php

namespace App\Filament\AvatarProviders;

use App\Support\Tema;
use Filament\AvatarProviders\Contracts\AvatarProvider;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * Avatar sa inicijalima, generisan lokalno kao SVG.
 *
 * Zamenjuje Filamentov podrazumevani UiAvatarsProvider, koji sliku traži sa
 * ui-avatars.com: kad taj servis ne odgovara, umesto avatara se vidi
 * polomljena slika, a usput mu se šalju inicijali svakog korisnika.
 */
class InicijaliAvatarProvider implements AvatarProvider
{
    public function get(Model|Authenticatable $record): string
    {
        $inicijali = str(Filament::getNameForDefaultAvatar($record))
            ->squish()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $deo): string => mb_strtoupper(mb_substr($deo, 0, 1)))
            ->join('');

        $pozadina = Tema::aktuelna()['primary-dark'];

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
            .'<rect width="64" height="64" fill="'.e($pozadina).'"/>'
            .'<text x="50%" y="50%" dy=".35em" text-anchor="middle" fill="#FFFFFF" '
            .'font-family="Inter, ui-sans-serif, system-ui, sans-serif" font-size="26" font-weight="600">'
            .e($inicijali)
            .'</text></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
