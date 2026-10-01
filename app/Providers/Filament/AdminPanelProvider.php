<?php

namespace App\Providers\Filament;

use App\Filament\AvatarProviders\InicijaliAvatarProvider;
use App\Support\Tema;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // Boje teme se čuvaju u bazi (tabela `podesavanja`)
        $tema = Tema::aktuelna();

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('UZRJ — Upravljanje članstvom')
            ->favicon(asset('images/favicon.ico'))
            ->defaultAvatarProvider(InicijaliAvatarProvider::class)
            ->colors([
                'primary' => Tema::paleta($tema['primary']),
                'secondary' => Tema::paleta($tema['accent']),
            ])
            // Filament inače nudi i svoj prekidač svetla/tamna/sistemski (pored
            // avatara), koji pamti izbor po pregledaču (localStorage) i od tog
            // trenutka trajno ignoriše podešavanje iz baze za taj pregledač —
            // zbog toga je "Tamni režim" u Konfiguraciji sistema delovao mrtav.
            // darkMode(condition, isForced) gasi taj prekidač i čini
            // tema_dark_mode jedinim izvorom istine za sve korisnike.
            ->darkMode(condition: $tema['dark_mode'], isForced: $tema['dark_mode'])
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
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): Htmlable => new HtmlString('<style id="theme-css">'.Tema::css().'</style>'),
            )
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
}
