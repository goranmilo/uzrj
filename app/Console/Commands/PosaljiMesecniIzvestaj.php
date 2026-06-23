<?php

namespace App\Console\Commands;

use App\Services\EmailService;
use Illuminate\Console\Command;

class PosaljiMesecniIzvestaj extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:mesecni-izvestaj';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pošalji mesečni izveštaj svim aktivnim članovima';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Slanje mesečnih izveštaja...');
        
        $poslato = EmailService::posaljiMesecniIzvestaj();
        
        $this->info("Završeno. Poslato {$poslato} izveštaja.");
        
        return Command::SUCCESS;
    }
}
