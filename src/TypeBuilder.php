<?php

namespace LaravelGraphQL;

use Illuminate\Support\Facades\Cache;
use phpDocumentor\Reflection\Types\Boolean;

class TypeBuilder
{
    protected string $queries = '';
    protected string $mutations = '';

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
    public function buildArgument(string $arg): string
    {
        $args = explode(',', str_replace(['@args', '*'], ['', ''], $arg));
        if (empty($args))
            return '';

        $buildArgs = [];
        foreach ($args as $a) {
            $arrArgs = explode(' ', trim($a));
            if (empty($arrArgs) || count($arrArgs) != 2)
                continue;

            $buildArgs[] = $arrArgs[1] . ': ' . $arrArgs[0];
        }
        if (empty($buildArgs))
            return '';

        return '(' . implode(', ', $buildArgs) . ')';
    }

    /**
     * Build type of query / mutation
     *
     * @param string $type
     * @return string
     */
    public function buildType(string $type): string
    {
        $type = str_replace(['@type', '*'], ['', ''], $type);
        return trim($type);
    }

    /**
     * Description of query / mutation
     *
     * @param string $description
     * @return string
     */
    public function buildDescription(string $description): string
    {
        $desc = str_replace(['@desc', '*'], ['', ''], $description);
        return trim($desc);
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
