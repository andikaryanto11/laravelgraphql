<?php

namespace LaravelGraphQL;

use GraphQL\Executor\Executor;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Container\Container;
use ReflectionClass;
use ReflectionMethod;

class GraphQL
{

    protected string $schema = '';
    protected array $queries = [];
    protected array $mutation = [];
    // protected array $types = [];
    // protected array $args = [];

    /**
     * @var Container
     */
    protected Container $app;

    /**
     * @var TypeBuilder
     */
    protected TypeBuilder $typeBuilder;

    /**
     * Undocumented function
     *
     * @param Container $app
     */
    public function __construct(
        Container $app, 
        TypeBuilder $typeBuilder)
    {
        $this->app = $app;
        $this->typeBuilder = $typeBuilder;
    }

    public function buildResolvers()
    {

        $this->setResolvers();
    }

    private function extractResolvers()
    {
        $pattern = "#(@[a-zA-Z]+\s*[a-zA-Z0-9, ()_].*)#";

        $resolvers = app('config')->get('graphql')['resolvers'];
        foreach ($resolvers as $resolver) {
            $this->app->make($resolver);
        }

        foreach ($resolvers as $resolver) {
            $reflector = (new ReflectionClass($resolver));
            $reflectorFunctions = $reflector->getMethods(ReflectionMethod::IS_FINAL);
            foreach ($reflectorFunctions as $reflectorFunction) {
                if ($reflectorFunction->name != '__construct') {

                    $doc = $reflectorFunction->getDocComment();
                    preg_match_all($pattern, $doc, $matches, PREG_PATTERN_ORDER);

                    if (!empty($matches)) {
                        if(count($matches) > 0 && isset($matches[0][0])){
                            $arg = $this->typeBuilder->buildArgument($matches[0][1]);
                            $type = $this->typeBuilder->buildType($matches[0][2]);
                            if (strpos($matches[0][0], '@query') !== false){
                                $this->queries[$reflectorFunction->name] = $this->app->get($resolver);
                                $this->typeBuilder->addQuery($reflectorFunction->name, $arg, $type);
                            } 
                            
                            if (strpos($matches[0][0], '@mutation') !== false){
                                $this->mutation[$reflectorFunction->name] = $this->app->get($resolver);
                                $this->typeBuilder->addMutation($reflectorFunction->name, $arg, $type);
                            }


                        }
                    }
                }
            }
        }
    }

    public function getResolvers(){
        $this->extractResolvers();
        return [
            'Query' => $this->queries,
            'Mutation' => $this->mutation
        ];
    }

    /**
     * build all schema
     *
     * @return \GraphQL\Type\Schema
     */
    public function buildSchema(){
        $this->buildDefaultSchema();
        $this->buildUserDefineSchema();
        return \GraphQL\Utils\BuildSchema::build($this->schema);
    }

    /**
     * build type
     *
     * @return void
     */
    private function buildDefaultSchema(): void
    {
        $this->schema .= $this->typeBuilder->build();
    }

    /**
     * build schema
     *
     * @return void
     */
    private function buildUserDefineSchema()
    {
        $result = array();
        $dir = app('config')->get('graphql')['schema_path'];
        $cdir = scandir($dir);
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", ".."))) {
                if (!is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $result[$value] = $value;
                    $this->schema .= "\n" . file_get_contents($dir . '/' . $value);
                }
            }
        }
    }

    /**
     * Set Resolver
     *
     * @return void
     */
    private function setResolvers()
    {
        $resolvers = $this->getResolvers();
        Executor::setDefaultFieldResolver(function ($source, $args, $context, ResolveInfo $info) use ($resolvers) {
            $fieldName = $info->fieldName;

            if (is_null($fieldName)) {
                throw new \Exception('Could not get $fieldName from ResolveInfo');
            }

            if (is_null($info->parentType)) {
                throw new \Exception('Could not get $parentType from ResolveInfo');
            }

            $parentTypeName = $info->parentType->name;

            if (isset($resolvers[$parentTypeName])) {
                $resolver = $resolvers[$parentTypeName];

                if (is_array($resolver)) {
                    if (array_key_exists($fieldName, $resolver)) {
                        $value = $resolver[$fieldName];

                        $value->setSource($source);
                        // $value->setArgs($args);
                        $value->setContext($source);
                        $value->setInfo($info);

                        $argsValues = array_values($args);
                        return $value->$fieldName(...$argsValues);

                        // return is_callable($value) ? $value($source, $args, $context, $info) : $value;
                    }
                }

                // if (is_object($resolver)) {
                //     if (isset($resolver->{$fieldName})) {
                //         $value = $resolver->{$fieldName};

                //         return is_callable($value) ? $value($source, $args, $context, $info) : $value;
                //     }
                // }
            }

            return Executor::defaultFieldResolver($source, $args, $context, $info);
        });
    }
}
