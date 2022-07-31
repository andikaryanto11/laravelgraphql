<?php

namespace LaravelGraphQL;

use Exception;
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
    private function buildArgument(string $arg): string
    {
        $args = explode(',', str_replace(['#args', '*'], ['', ''], $arg));
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
    private function buildType(string $type): string
    {
        $type = str_replace(['#type', '*'], ['', ''], $type);
        return trim($type);
    }

    /**
     * Description of query / mutation
     *
     * @param string $description
     * @return string
     */
    private function buildDescription(string $description): string
    {
        $desc = str_replace(['#desc', '*'], ['', ''], $description);
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
     *
     * @param array $docs
     * @param string $resolverType
     * @return array
     */
    public function buildResolverParamsFromAnnotation(string $resolverName, string $doc, string &$resolverType): array
    {

        $resolverArgs = [];
        $pattern = "(#[a-zA-Z]+\ *[a-zA-Z0-9, ()_].*)";
        
        $hasQuery = false;
        $hasMutation = false;
        $hasType = false;
        $isResolverValid = true;
        preg_match_all($pattern, $doc, $matches, PREG_PATTERN_ORDER);
        if (!empty($matches)) {
            foreach ($matches[0] as $annotation) {
                if (strpos($annotation, '#args') !== false) {
                    $resolverArgs['args'] = $this->buildArgument($annotation);
                } elseif (strpos($annotation, '#type') !== false) {
                    $hasType = true;
                    $resolverArgs['type'] = $this->buildType($annotation);
                } elseif (strpos($annotation, '#desc') !== false) {
                    $resolverArgs['desc'] = $this->buildDescription($annotation);
                } elseif (strpos($annotation, '#query') !== false) {
                    $hasQuery = true;
                    $resolverType = 'query';
                } elseif (strpos($annotation, '#mutation') !== false) {
                    $hasMutation = true;
                    $resolverType = 'mutation';
                }
            }
            $isResolverValid = $hasType && ($hasMutation || $hasQuery);

            if(!$isResolverValid){
                throw new GraphQLException($resolverName . ' needs mandatory annotation #query, #mutation, #type');
            }
        }

        return $resolverArgs;
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
