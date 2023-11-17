<?php

namespace LaravelGraphQL\Inputs;

class FilterInput extends AbstractInput
{
    protected string $search = '';

    public function __construct()
    {
    }

    /**
     * Get the value of search
     */
    public function getSearch()
    {
        return $this->search;
    }
}
