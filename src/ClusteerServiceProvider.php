<?php

namespace RenokiCo\Clusteer;

use Illuminate\Support\ServiceProvider;

class ClusteerServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/clusteer.php' => config_path('clusteer.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../config/clusteer.php', 'clusteer'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\ServeClusteer::class,
            ]);
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
