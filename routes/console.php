<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Zakazano slanje mejlova
Schedule::command('email:mesecni-izvestaj')
    ->monthly()
    ->description('Slanje mesečnih izveštaja članovima');

Schedule::command('email:podsetnik-clanarine')
    ->weekly()
    ->description('Slanje podsetnika za neplaćenu članarinu');

Schedule::command('email:upozorenje-bodovi')
    ->monthly()
    ->description('Slanje upozorenja za članove ispod minimuma bodova');
