<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\View;
use Throwable;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app()->setLocale('es');

        View::composer('*', function ($view): void {
            try {
                $view->with('siteSettings', SiteSetting::current());
            } catch (Throwable) {
                $view->with('siteSettings', null);
            }
        });
    }
}
