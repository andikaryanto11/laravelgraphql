<?php

namespace LaravelGraphQL\App\Providers;

use Illuminate\Support\ServiceProvider;

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
       
    }
}
