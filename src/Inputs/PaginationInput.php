<?php

namespace LaravelGraphQL\Inputs;

class PaginationInput extends AbstractInput
{
    protected int $page = 1;
    protected int $size = 25;

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
}
