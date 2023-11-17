<?php

namespace LaravelGraphQL\App\Providers;

use Illuminate\Support\ServiceProvider;
use LaravelGraphQL\App\Console\Commands\ClearGraphqlSchemaCache;

class GraphQLProvider extends ServiceProvider
{
    public $bindings = [

    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        $this->loadRoutesFrom(__DIR__ . '/../../Routes/graphql.php');
        $this->publishConfig();

        if ($this->app->runningInConsole()) {
            $this->commands([
                ClearGraphqlSchemaCache::class,
            ]);
        }
    }

    /**
     * Publish all config
     *
     * @return void
     */
    private function publishConfig()
    {
        $this->publishes([
            __DIR__ . '/../../Config/graphql.php' => config_path('graphql.php'),
        ], 'graphql-config');
    }
}
