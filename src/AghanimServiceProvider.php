<?php

namespace BangNokia\Aghanim;

use Illuminate\Support\ServiceProvider;
use BangNokia\Aghanim\Console\Commands\GenerateAghanimActions;

class AghanimServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/aghanim.php', 'aghanim');
    }

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
