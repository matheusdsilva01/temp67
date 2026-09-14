<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\DevCommands;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        if ($this->app->runningInConsole()) {
            DevCommands::register(
                'php -d upload_max_filesize=10M -d post_max_size=11M artisan serve',
                'server',
            );
            DevCommands::artisan('schedule:work', 'scheduler');
        }

        RateLimiter::for('files', function (Request $request): Limit {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
