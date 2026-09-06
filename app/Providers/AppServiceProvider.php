<?php

namespace App\Providers;

use Illuminate\Foundation\DevCommands;
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
    }
}
