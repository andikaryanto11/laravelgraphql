<?php

namespace LaravelGraphQL\Inputs;

class Pagination extends AbstractInput
{
    private int $page;
    private int $size;

    public function __construct()
    {
        $this->page = 1;
        $this->size = 25;
    }

    public function getInput(): string
    {
        return 'PaginationInput = {page: 1 size:25}';
    }

    /**
     * Get the value of page
     */ 
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * Get the value of size
     */ 
    public function getSize(): int
    {
        return $this->size;
    }

    public function parseFromArray(array $values)
    {
        foreach($values as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }
}
