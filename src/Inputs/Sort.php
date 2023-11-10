<?php

namespace LaravelGraphQL\Inputs;

class Sort extends AbstractInput
{
    private string $field = '';
    private string $direction = 'ASC';

    public function __construct()
    {
    }

    public function getInput(): string
    {
        return 'SortInput = {field: "" direction: "DESC"}';
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

    public function parseFromArray(array $values)
    {
        foreach($values as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }
}
