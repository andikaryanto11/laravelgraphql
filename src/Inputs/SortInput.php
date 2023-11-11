<?php

namespace LaravelGraphQL\Inputs;

class SortInput extends AbstractInput
{
    protected string $field = '';
    protected string $direction = 'ASC';

    public function __construct()
    {
    }

    /**
     * Get the value of field
     */ 
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Get the value of direction
     */ 
    public function getDirection(): string
    {
        return $this->direction;
    }
}
