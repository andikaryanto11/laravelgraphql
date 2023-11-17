<?php

namespace LaravelGraphQL;

class GraphQLMiddleware
{
    protected $middleware = null;
    protected array $scopes = [];

    /**
     * set middleware
     *
     * @return $this
     */
    public function setMiddleware($middleware): GraphQLMiddleware
    {
        $this->middleware = $middleware;
        return $this;
    }

    /**
     * set middleware scope
     *
     * @param array $scope
     * @return $this
     */
    public function setScope(array $scopes): GraphQLMiddleware
    {
        $this->scopes = $scopes;
        return $this;
    }

    /**
     *
     * @return mixed
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getScope(): array
    {
        return $this->scopes;
    }
}
