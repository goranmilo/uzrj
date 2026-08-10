<?php

namespace Tests\Unit;

use App\Support\Tema;
use PHPUnit\Framework\TestCase;

class TemaTest extends TestCase
{
    public function test_validira_hex_boju_i_vraca_fallback_za_neispravnu(): void
    {
        $this->assertSame('#0591F0', Tema::hex('#0591F0'));
        $this->assertSame('#0591f0', Tema::hex('0591f0'));
        $this->assertSame('#aabbcc', Tema::hex('#abc'));
        $this->assertSame(Tema::PRIMARY, Tema::hex(''));
        $this->assertSame(Tema::PRIMARY, Tema::hex(null));
        $this->assertSame(Tema::PRIMARY, Tema::hex('nije-boja'));
        $this->assertSame('#000000', Tema::hex('crveno', '#000000'));
    }

    public function test_paleta_daje_sve_nijanse_kao_rgb_trojke(): void
    {
        $paleta = Tema::paleta('#0591f0');

        $this->assertSame([50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950], array_keys($paleta));

        foreach ($paleta as $nijansa => $vrednost) {
            $this->assertMatchesRegularExpression(
                '/^\d{1,3}, \d{1,3}, \d{1,3}$/',
                $vrednost,
                "Nijansa {$nijansa} nije u formatu 'r, g, b'.",
            );
        }

        // Nijansa 500 je sama zadata boja, 50 je znatno svetlija, 950 tamnija.
        $this->assertSame('5, 145, 240', $paleta[500]);
        $this->assertSame('2, 44, 72', $paleta[950]);
    }

    public function test_paleta_ne_puca_na_neispravnoj_boji(): void
    {
        $this->assertSame(Tema::paleta(Tema::PRIMARY), Tema::paleta('###'));
    }

    public function test_css_sadrzi_varijable_teme(): void
    {
        $css = Tema::css([
            'primary' => '#0591f0',
            'primary-dark' => '#036fb8',
            'accent' => '#6ddbfc',
            'dark_mode' => false,
        ]);

        $this->assertStringContainsString('--theme-primary: #0591f0;', $css);
        $this->assertStringContainsString('--theme-primary-rgb: 5, 145, 240;', $css);
        $this->assertStringContainsString('--theme-primary-dark: #036fb8;', $css);
        $this->assertStringContainsString('--theme-accent: #6ddbfc;', $css);

        // Stari zapis je lepio sufiks na hex (alfa kanal umesto nijanse).
        $this->assertStringNotContainsString('#0591f020', $css);
    }
}
