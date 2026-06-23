<?php

namespace App\Providers\Filament;

use App\Models\Podesavanje;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // Dobijanje boje iz baze
        $temaKey = Podesavanje::get('tema', 'emerald');
        $primaryColor = $this->getTemaColor($temaKey);

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('UZRJ — Upravljanje članstvom')
            ->favicon(asset('images/favicon.ico'))
            ->colors([
                'primary' => $primaryColor,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Članstvo')
                    ->icon('heroicon-o-users'),
                NavigationGroup::make()
                    ->label('Edukacije')
                    ->icon('heroicon-o-academic-cap'),
                NavigationGroup::make()
                    ->label('Finansije')
                    ->icon('heroicon-o-banknotes'),
                NavigationGroup::make()
                    ->label('Izveštaji')
                    ->icon('heroicon-o-document-chart-bar'),
                NavigationGroup::make()
                    ->label('Administracija')
                    ->icon('heroicon-o-cog-6-tooth'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->sidebarCollapsibleOnDesktop();
    }

    /**
     * Dobij Filament Color objekat za temu
     */
    protected function getTemaColor(string $temaKey): array
    {
        // Mapiranje tema na Filament boje
        $temeBoje = [
            'emerald' => Color::Emerald,
            'blue' => Color::Blue,
            'purple' => Color::Violet,
            'red' => Color::Red,
            'orange' => Color::Orange,
            'teal' => Color::Teal,
            'pink' => Color::Pink,
            'dark' => Color::Indigo,
        ];

        // Provera za custom boju
        $customPrimary = Podesavanje::get('tema_primary');
        if ($customPrimary) {
            // Filament prihvata hex boje kao nijanse
            return [
                50 => $customPrimary . '10',
                100 => $customPrimary . '20',
                200 => $customPrimary . '30',
                300 => $customPrimary . '40',
                400 => $customPrimary . '50',
                500 => $customPrimary,
                600 => $customPrimary . '70',
                700 => $customPrimary . '80',
                800 => $customPrimary . '90',
                900 => $customPrimary . '95',
                950 => $customPrimary . '99',
            ];
        }

        return $temeBoje[$temaKey] ?? Color::Emerald;
    }
}
