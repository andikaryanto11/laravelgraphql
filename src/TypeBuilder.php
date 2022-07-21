<?php

namespace LaravelGraphQL;


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
    public function addQuery(string $name, string $arg, string $type, $description)
    {
        $this->queries .= '"""' . $description . '"""'. " \n $name $arg: $type \n";
    }

    /**
     * Add string mutation
     *
     * @param string $mutation
     * @return void
     */
    public function addMutation(string $name, string $arg, string $type, $description)
    {
        $this->mutations .= '"""' . $description . '"""'. " \n $name $arg: $type \n";
    }

    public function buildArgument(string $arg){
        $args = explode(',', str_replace(['@args', '*'], ['', ''], $arg));
        if(empty($args))
            return '';

        $buildArgs = [];
        foreach($args as $a){
            $arrArgs = explode(' ', trim($a));
            if(empty($arrArgs) || count($arrArgs) != 2)
                continue;

            $buildArgs[] = $arrArgs[1] . ': ' . $arrArgs[0];
        }
        if(empty($buildArgs))
            return '';

        return '(' . implode($buildArgs) . ')';
    }

    public function buildType($type){
        $type = str_replace(['@type', '*'], ['', ''], $type);
        return trim($type);
    }

    public function buildDescription($description){
        $desc = str_replace(['@desc', '*'], ['', ''], $description);
        return trim($desc);
    }

    /**
     * Build query and mutation type
     *
     * @return void
     */
    public function build()
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
    private function buildQuery(string $strQueries)
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
    private function buildMutation(string $strMutations)
    {
        return "type Mutation {\n" .
            $strMutations . "\n" .
            '}';
    }
}
