<?php

namespace BangNokia\Aghanim;

use BangNokia\Aghanim\Console\Commands\GenerateAghanimActions;
use BangNokia\Aghanim\Security\ActionAuthorizer;
use Illuminate\Support\ServiceProvider;

class AghanimServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/aghanim.php', 'aghanim');

        // Register the ActionAuthorizer
        $this->app->singleton(ActionAuthorizer::class, function ($app) {
            return new ActionAuthorizer();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateAghanimActions::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/aghanim.php' => config_path('aghanim.php'),
        ], 'aghanim-config');

        $this->publishes([
            __DIR__.'/../resources/dist' => public_path('vendor/aghanim'),
        ], 'aghanim-assets');
    }
}
