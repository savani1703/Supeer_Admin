<?php

namespace App\Providers;

use App\Plugin\AccessControl\Utils\AccessControlUtils;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer(
            'layout.master',
            function ($view) {
                if(Auth::check()) {
                    $sidebarData = AccessControlUtils::renderSidebar();
                    $view->with('sidebarData', $sidebarData);
                }
            }
        );
    }
}
