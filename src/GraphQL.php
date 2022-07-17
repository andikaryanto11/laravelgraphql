<?php

namespace LaravelGraphQL;
use Illuminate\Contracts\Container\Container;
use ReflectionClass;

class GraphQL {

    protected array $queries= [];
    protected array $mutation = [];
    protected array $types = [];
    protected array $args = [];

    /**
     * @var Container
     */
    protected Container $app;

    /**
     * Undocumented function
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    public function buildResolver(){
        $resolvers = app('config')->get('graphql')['resolvers'];

        foreach($resolvers as $resolver){

            $reflection = (new ReflectionClass($resolver))->getMethods();

        }
    }

    public function buildType(){

    }

}