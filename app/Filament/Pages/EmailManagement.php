<?php

namespace App\Filament\Pages;

use App\Services\EmailService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class EmailManagement extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Administracija';
    protected static ?string $navigationLabel = 'Upravljanje mejlovima';
    protected static ?string $title = 'Upravljanje mejlovima';
    protected static ?int $navigationSort = 17;

    protected static string $view = 'filament.pages.email-management';

    public function posaljiMesecniIzvestaj(): void
    {
        $poslato = EmailService::posaljiMesecniIzvestaj();

        Notification::make()
            ->title('Mesečni izveštaji')
            ->body("Poslato je {$poslato} izveštaja.")
            ->success()
            ->send();
    }

    public function posaljiPodsetnikClanarine(): void
    {
        $poslato = EmailService::posaljiPodsetnikClanarine();

        Notification::make()
            ->title('Podsetnici za članarinu')
            ->body("Poslato je {$poslato} podsetnika.")
            ->success()
            ->send();
    }

    public function posaljiUpozorenjeBodovi(): void
    {
        $poslato = EmailService::posaljiUpozorenjeBodovi();

        Notification::make()
            ->title('Upozorenja za bodove')
            ->body("Poslato je {$poslato} upozorenja.")
            ->success()
            ->send();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }
}
