<?php

namespace App\Providers;

use App\Nova\User;
use App\Nova\Owner;
use App\Nova\Catalog;
use Laravel\Nova\Nova;
use App\Nova\CatalogArea;
use App\Nova\CatalogType;
use Illuminate\Http\Request;
use App\Nova\CadastralParcel;
use App\Nova\Dashboards\Main;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Nova::mainMenu(function (Request $request) {
            return [
                MenuSection::dashboard(Main::class)
                    ->icon('home'),

                MenuSection::make('ADMIN', [
                    MenuItem::resource(User::class)
                ])->icon('user')->collapsable(),

                MenuSection::make('SISTECO', [
                    MenuItem::resource(Owner::class),
                    MenuItem::resource(CadastralParcel::class)
                ])->icon('globe')->collapsable(),


                MenuSection::make('CATALOG', [
                    MenuItem::resource(Catalog::class),
                    MenuItem::resource(CatalogType::class),
                    MenuItem::resource(CatalogArea::class),
                ])
                    ->icon('book-open')->collapsable(),
            ];
        });
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
            ->withAuthenticationRoutes()
            ->withPasswordResetRoutes()
            ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return in_array($user->email, [
                //
            ]);
        });
    }

    /**
     * Get the dashboards that should be listed in the Nova sidebar.
     *
     * @return array
     */
    protected function dashboards()
    {
        return [
            new \App\Nova\Dashboards\Main,
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
