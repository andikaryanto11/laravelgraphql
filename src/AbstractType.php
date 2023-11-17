<?php

namespace LaravelGraphQL;

abstract class AbstractType
{
    /**
     * Type name
     *
     * @return string
     */
    abstract public function name();

    /**
     * Type description
     *
     * @return string
     */
    abstract public function description();

    /**
     * Undocumented function
     *
     * @return array
     */
    abstract public function fields();
}
