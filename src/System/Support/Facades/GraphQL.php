<?php

namespace LaravelGraphQL\System\Support\Facades;

use Illuminate\Support\Facades\Facade;
use LaravelGraphQL\GraphQL as RealGraphQL;

/**
 * @method mixed buildResolver()
 */
class GraphQL extends Facade
{
    /**
     * 
     * @inheritdoc
     */
    protected static function getFacadeAccessor()
    {
        return RealGraphQL::class;
    }
}