<?php

namespace App\Support;

use App\Filament\Pages\ThemeSettings;
use Filament\Support\Colors\Color;

/**
 * Jedinstveno mesto za obradu boja teme.
 *
 * Filament očekuje paletu nijansi u formatu "r, g, b" po nijansi (50–950),
 * pa se hex boja iz podešavanja mora prevesti u pravu paletu — lepljenje
 * sufiksa na hex string daje alfa kanal, a ne svetliju/tamniju nijansu.
 */
class Tema
{
    public const PRIMARY = '#10B981';

    public const PRIMARY_DARK = '#059669';

    public const ACCENT = '#34D399';

    /**
     * Aktuelna tema, otporna na nedostupnu bazu (panel se registruje pre migracija).
     *
     * @return array{primary: string, primary-dark: string, accent: string, dark_mode: bool}
     */
    public static function aktuelna(): array
    {
        try {
            $tema = ThemeSettings::getAktuelnaTema();
        } catch (\Throwable) {
            $tema = [];
        }

        return [
            'primary' => static::hex($tema['primary'] ?? null, static::PRIMARY),
            'primary-dark' => static::hex($tema['primary-dark'] ?? null, static::PRIMARY_DARK),
            'accent' => static::hex($tema['accent'] ?? null, static::ACCENT),
            'dark_mode' => (bool) ($tema['dark_mode'] ?? false),
        ];
    }

    /**
     * Generiši Filament paletu (nijanse 50–950) iz hex boje.
     *
     * @return array<int, string>
     */
    public static function paleta(?string $hex): array
    {
        return Color::hex(static::hex($hex, static::PRIMARY));
    }

    /**
     * Vrati validnu hex boju ili prosleđeni fallback.
     */
    public static function hex(?string $vrednost, string $fallback = self::PRIMARY): string
    {
        $vrednost = trim((string) $vrednost);

        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $vrednost)) {
            return $vrednost;
        }

        // Dozvoli i skraćeni zapis (#abc) i zapis bez tarabe.
        if (preg_match('/^#?([0-9A-Fa-f]{3})$/', $vrednost, $m)) {
            [$r, $g, $b] = str_split($m[1]);

            return '#'.$r.$r.$g.$g.$b.$b;
        }

        if (preg_match('/^([0-9A-Fa-f]{6})$/', $vrednost, $m)) {
            return '#'.$m[1];
        }

        return $fallback;
    }

    /**
     * "r, g, b" zapis boje — format koji koriste Filament CSS varijable.
     */
    public static function rgb(?string $hex): string
    {
        $hex = ltrim(static::hex($hex), '#');

        return implode(', ', [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ]);
    }

    /**
     * CSS varijable teme za korišćenje u Blade prikazima aplikacije.
     *
     * Primarnu boju Filament panela postavlja AdminPanelProvider kroz paletu,
     * pa ovde nema `!important` prepisivanja Filament klasa.
     *
     * @param  array{primary?: string, primary-dark?: string, accent?: string, dark_mode?: bool}|null  $tema
     */
    public static function css(?array $tema = null): string
    {
        $tema ??= static::aktuelna();

        $primary = static::hex($tema['primary'] ?? null, static::PRIMARY);
        $primaryDark = static::hex($tema['primary-dark'] ?? null, static::PRIMARY_DARK);
        $accent = static::hex($tema['accent'] ?? null, static::ACCENT);
        $primaryRgb = static::rgb($primary);
        $accentRgb = static::rgb($accent);

        return <<<CSS
            :root {
                --theme-primary: {$primary};
                --theme-primary-rgb: {$primaryRgb};
                --theme-primary-dark: {$primaryDark};
                --theme-accent: {$accent};
                --theme-accent-rgb: {$accentRgb};
            }
            CSS;
    }
}
