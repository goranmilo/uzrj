<?php

namespace App\Console\Commands;

use App\Services\EmailService;
use Illuminate\Console\Command;

class PosaljiPodsetnikClanarine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:podsetnik-clanarine';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pošalji podsetnik za neplaćenu članarinu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Slanje podsetnika za članarinu...');
        
        $poslato = EmailService::posaljiPodsetnikClanarine();
        
        $this->info("Završeno. Poslato {$poslato} podsetnika.");
        
        return Command::SUCCESS;
    }
}
