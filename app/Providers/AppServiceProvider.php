<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\DevCommands;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
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
        // "composer run dev" starts a queue worker by default. This application queues
        // nothing and has no jobs table, so the pane would sit there with no work.
        DevCommands::except('queue');

        // Outside production, reading a relationship that was not eager loaded throws
        // instead of quietly running one query per row, so an N+1 query fails the test
        // or page that caused it.
        Model::preventLazyLoading(! $this->app->isProduction());

        RateLimiter::for('recipe-api', function (Request $request) {
            return Limit::perMinute(config('api.recipe_search_per_minute'))->by($request->ip());
        });

        // The end-to-end tests serve the built assets even while a developer's Vite server
        // is running, by pointing Laravel at a hot file that does not exist.
        if ($hotFile = config('app.vite_hot_file')) {
            Vite::useHotFile($hotFile);
        }
    }
}
