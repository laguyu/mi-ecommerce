<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Throwable;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Support/route_helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (isset($_ENV['VERCEL'])) {
            // Creamos las carpetas dinámicamente en el directorio temporal si no existen
            $paths = [
                '/tmp/storage/framework/views',
                '/tmp/storage/framework/cache',
                '/tmp/storage/framework/sessions',
                '/tmp/storage/bootstrap/cache',
                '/tmp/storage/app/public',
            ];
            foreach ($paths as $path) {
                if (!is_dir($path)) {
                    mkdir($path, 0755, true);
                }
            }

            // Reconfiguramos las rutas de guardado en caliente
            Config::set('view.compiled', '/tmp/storage/framework/views');
            Config::set('cache.stores.file.path', '/tmp/storage/framework/cache');
            Config::set('session.files', '/tmp/storage/framework/sessions');

            // En Vercel preferimos S3 si hay credenciales; si no, public queda en /tmp para evitar fallos de escritura.
            $hasS3Credentials = filled(env('AWS_ACCESS_KEY_ID'))
                && filled(env('AWS_SECRET_ACCESS_KEY'))
                && filled(env('AWS_BUCKET'))
                && filled(env('AWS_ENDPOINT'));

            if ($hasS3Credentials) {
                Config::set('filesystems.default', 's3');
            }

            Config::set('filesystems.disks.public.root', '/tmp/storage/app/public');
        }


        app()->setLocale('es');

        if (app()->environment('production') || env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }

        View::composer('*', function ($view): void {
            try {
                $view->with('siteSettings', SiteSetting::current());
            } catch (Throwable) {
                $view->with('siteSettings', null);
            }
        });
    }
}
