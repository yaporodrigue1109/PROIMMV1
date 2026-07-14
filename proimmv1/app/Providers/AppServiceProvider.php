<?php

namespace App\Providers;

use App\Jobs\GenerateLoyerMensuel;
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
        if (filter_var(env('AUTO_GENERATE_LOYER', false), FILTER_VALIDATE_BOOL)) {
            GenerateLoyerMensuel::dispatchSync(now(), null);
        }
    }
}
