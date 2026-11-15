<?php

namespace App\Providers;

use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        // Render atiende por HTTPS detrás de un proxy.
        TrustProxies::at('*');

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
