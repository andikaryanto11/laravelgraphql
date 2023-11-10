<?php

namespace LaravelGraphQL;

use Exception;
use Illuminate\Support\Facades\Cache;
use LaravelGraphQL\Types\GraphQLCollection;
use phpDocumentor\Reflection\Types\Boolean;

use function PHPSTORM_META\type;

class TypeBuilder
{
    private const ANOTATION_EXTRACTOR_REGEX = '(#[a-zA-Z]+\ *[a-zA-Z0-9, ()_].*)';
    protected string $queries = '';
    protected string $mutations = '';
    protected array $collectionTypes = [];

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
    public function buildType(mixed $type): string
    {
        if (is_array($type)) {
            $this->collectionTypes[] = GraphQLCollection::of($type[0]);
            return 'GraphQLCollection' . $type[0];
        }

        return $type;
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

        foreach($this->collectionTypes as $collectionType) {
            $type .= $collectionType;
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
