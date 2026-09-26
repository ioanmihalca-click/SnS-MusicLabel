<?php

namespace App\Providers;

use App\Support\Seo\LeagueDriverWithTables;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registered after the package's own binding, which it replaces.
        $this->app->singleton('markdown-response.driver.league', fn (): LeagueDriverWithTables => new LeagueDriverWithTables(
            config('markdown-response.driver_options.league.options', []),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->useAppUrlInProduction();
    }

    /**
     * Generate every URL from `app.url` (https, no `www`), whatever host or
     * scheme the request arrived with behind the hosting proxy.
     */
    private function useAppUrlInProduction(): void
    {
        if (! $this->app->isProduction()) {
            return;
        }

        $appUrl = (string) config('app.url');

        URL::forceRootUrl($appUrl);

        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }
    }
}
