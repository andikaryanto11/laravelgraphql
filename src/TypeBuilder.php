<?php

namespace LaravelGraphQL;

use LaravelGraphQL\Attributes\Types\PagedCollectionType;

class TypeBuilder
{
    private const ANOTATION_EXTRACTOR_REGEX = '(#[a-zA-Z]+\ *[a-zA-Z0-9, ()_].*)';
    protected string $queries = '';
    protected string $mutations = '';
    protected array $collectionTypes = [];
    protected array $inputClasses = [];

    /**
     * Add string query
     *
     * @param string $query
     * @return void
     */
    public function addQuery(string $name, string $arg, string $type, $description): void
    {
        $this->queries .= '"""' . $description . '"""' . " \n $name $arg: $type \n";
    }

    /**
     * Add string mutation
     *
     * @param string $mutation
     * @return void
     */
    public function addMutation(string $name, string $arg, string $type, $description): void
    {
        $this->mutations .= '"""' . $description . '"""' . " \n $name $arg: $type \n";
    }

    /**
     * build args of query / muatation
     *
     * @param string $arg
     * @return string
     */
    public function buildArgument(array $args): string
    {
        if (empty($args))
            return '';

        return '(' . implode(', ', $args) . ')';
    }

    /**
     * Build type of query / mutation
     *
     * @param string $type
     * @return string
     */
    public function buildCollectionType(mixed $type): string
    {
        return '[' . $type . ']';
    }
    
    /**
     * Build type of query / mutation
     *
     * @param string $type
     * @return string
     */
    public function buildPagedCollectionType(mixed $type): string
    {
        $this->collectionTypes[] = PagedCollectionType::of($type);
        return 'PagedCollectionType' . $type;
    }
    
    /**
     * Build type of query / mutation
     *
     * @param string $type
     * @return string
     */
    public function buildType(mixed $type): string
    {
        return $type;
    }

    public function addInputClasses($class)
    {
        $isAdded = false;
        foreach ($this->inputClasses as $inputClass) {
            if (get_class($inputClass) == get_class($class)) {
                $isAdded = true;
            }
        }

        if (!$isAdded) {
            $this->inputClasses[] = $class;
        }
    }

    /**
     * Build query and mutation type
     *
     * @return void
     */
    public function build(): string
    {
        $type = '';
        if (!empty($this->queries))
            $type .= $this->buildQuery($this->queries);

        if (!empty($this->mutations))
            $type .= "\n\n" . $this->buildMutation($this->mutations);

        foreach ($this->collectionTypes as $collectionType) {
            $type .= $collectionType;
        }

        foreach ($this->inputClasses as $inputClass) {
            $type .= $inputClass->generateInput();
        }

        return $type;
    }

    /**
     * Build query type
     *
     * @param string $strQueries
     * @return string
     */
    private function buildQuery(string $strQueries): string
    {
        return "type Query {\n" .
            $strQueries . "\n" .
            '}';
    }

    /**
     * Build mutation type
     *
     * @param string $strMutations
     * @return string
     */
    private function buildMutation(string $strMutations): string
    {
        return "type Mutation {\n" .
            $strMutations . "\n" .
            '}';
    }
}
