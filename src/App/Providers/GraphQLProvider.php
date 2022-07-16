<?php

namespace LaravelGraphQL\App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use LaravelCommon\App\Http\Middleware\ControllerAfter;
use LaravelCommon\App\Http\Middleware\TokenValid;
use LaravelCommon\System\Database\Schema\Blueprint as SchemaBlueprint;
use LaravelCommon\System\Http\Request\Request as RequestRequest;

class GraphQLProvider extends ServiceProvider
{
    public $bindings = [
        // RequestRequest::class => Request::class,
        Blueprint::class => SchemaBlueprint::class
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
