<?php

namespace App\Providers\Filament;


use App\Filament\Widgets\Calendar;
use App\Filament\Widgets\courses;
use App\Filament\Widgets\PlacementTests;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Pages\Auth\Login;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->plugins([FilamentFullCalendarPlugin::make()])
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->colors([
                'primary' => Color::Amber,
                'danger' => Color::Rose,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'success' => Color::Emerald,
                'warning' =>Color::Orange,
            ])
           ->path('admin')
//            ->tenant(RegisterCourse::class)
//            ->tenantRoutePrefix('team')
            ->sidebarCollapsibleOnDesktop()
            ->breadcrumbs(false)
            ->databaseNotifications()
            ->databaseNotificationsPolling('15s')
//            ->theme(asset('css/filament/admin/theme.css'))
            ->plugin(
                FilamentFullCalendarPlugin::make()
                    ->timezone(config('app.timezone'))
                    ->plugins(['timeGrid', 'dayGrid','interaction','list'], false)
                    ->selectable()
                    ->config([
                        'initialView' => 'dayGridMonth',
                        'headerToolbar' => [
                            'start' => 'prev,next today',
                            'center' => 'title',
                            'end' => 'dayGridMonth,timeGridWeek,timeGridDay,listDay',
                        ],
                        'dayMaxEventRows' => true,
                        'views' => [
                            'dayGridMonth' => [
                                'dayMaxEventRows' => 4,
                            ]
                        ],

                    ])
            )
            ->plugin(
                \Hasnayeen\Themes\ThemesPlugin::make(),
                //    ->canViewThemesPage(fn () => auth()->user()->is_admin == 1)
            )
           //->brandLogo(asset('images/MyImages/'))

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets(
                [
                    Calendar::class,
                    PlacementTests::class,
                    courses::class,
                ]
            )
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
                \Hasnayeen\Themes\Http\Middleware\SetTheme::class
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
            //->theme(asset('css/filament/admin/theme.css'));

    }
}
