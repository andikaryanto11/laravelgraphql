<?php

namespace LaravelGraphQL\Inputs;

abstract class AbstractInput {
    public abstract function getInput();
    public abstract function parseFromArray(array $values);
}