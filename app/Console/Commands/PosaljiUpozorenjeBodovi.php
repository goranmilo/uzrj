<?php

namespace App\Console\Commands;

use App\Services\EmailService;
use Illuminate\Console\Command;

class PosaljiUpozorenjeBodovi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:upozorenje-bodovi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pošalji upozorenje članovima ispod minimuma bodova';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Slanje upozorenja za bodove...');
        
        $poslato = EmailService::posaljiUpozorenjeBodovi();
        
        $this->info("Završeno. Poslato {$poslato} upozorenja.");
        
        return Command::SUCCESS;
    }
}
