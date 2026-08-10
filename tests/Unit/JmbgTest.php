<?php

namespace Tests\Unit;

use App\Rules\Jmbg;
use PHPUnit\Framework\TestCase;

class JmbgTest extends TestCase
{
    private function greske(string $jmbg): array
    {
        $greske = [];

        (new Jmbg)->validate('jmbg', $jmbg, function (string $poruka) use (&$greske) {
            $greske[] = $poruka;
        });

        return $greske;
    }

    public function test_prihvata_ispravan_jmbg(): void
    {
        $this->assertSame([], $this->greske('0101990500011'));
    }

    public function test_odbija_pogresnu_kontrolnu_cifru(): void
    {
        $this->assertNotEmpty($this->greske('0101990500012'));
    }

    public function test_odbija_pogresan_format_i_datum(): void
    {
        $this->assertNotEmpty($this->greske('123'));
        $this->assertNotEmpty($this->greske('01019905000ab'));
        $this->assertNotEmpty($this->greske('3202990500011'));
    }
}
