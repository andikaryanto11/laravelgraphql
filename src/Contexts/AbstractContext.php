<?php

namespace LaravelGraphQL\Contexts;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

abstract class AbstractContext {

    /**
     * Make context 
     *
     * @param Request $request
     * @param mixed $context
     * @return void
     */
    abstract public function make(Request $request, $context);

}