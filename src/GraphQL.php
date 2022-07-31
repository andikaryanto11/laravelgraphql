<?php

namespace LaravelGraphQL;

use GraphQL\Executor\Executor;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use LaravelCommon\ViewModels\AbstractCollection;
use LaravelCommon\ViewModels\AbstractViewModel;
use LaravelGraphQL\Contexts\Token;
use ReflectionClass;
use ReflectionMethod;

class GraphQL
{

    public const CACHE_RESOLVERS = 'laravelgraphql_resolvers';
    public const CACHE_SCHEMAS = 'laravelgraphql_schemas';
    protected string $schema = '';
    protected array $queries = [];
    protected array $mutation = [];

    /**
     * @var Container
     */
    protected Container $app;

    /**
     * @var TypeBuilder
     */
    protected TypeBuilder $typeBuilder;

    /**
     * @var Context
     */
    protected Context $graphqlContext;

    /**
     * @var Token
     */
    protected Token $contextToken;

    /**
     * Undocumented function
     *
     * @param Container $app
     */
    public function __construct(
        Container $app,
        TypeBuilder $typeBuilder,
        Context $graphqlContext,
        Token $contextToken
    ) {
        $this->app = $app;
        $this->typeBuilder = $typeBuilder;
        $this->graphqlContext = $graphqlContext;
        $this->contextToken = $contextToken;
    }

    /**
     * Extract all resolver function from all classes registered
     *
     * @return void
     */
    private function extractResolvers()
    {

        $resolvers = app('config')->get('graphql')['resolvers'];

        foreach ($resolvers as $resolver) {
            $this->app->make($resolver);
            $reflector = (new ReflectionClass($resolver));
            $reflectorFunctions = $reflector->getMethods(ReflectionMethod::IS_FINAL);
            foreach ($reflectorFunctions as $reflectorFunction) {
                if ($reflectorFunction->name != '__construct') {

                    $doc = $reflectorFunction->getDocComment();
                    $resolverType = '';
                    $docs = $this->typeBuilder->buildResolverParamsFromAnnotation($reflectorFunction->name, $doc, $resolverType);

                    if (!empty($docs)) {
                        if($resolverType == 'query'){
                            $this->queries[$reflectorFunction->name] = $this->app->get($resolver);
                            $this->typeBuilder->addQuery($reflectorFunction->name, $docs['args'], $docs['type'], $docs['desc']);
                        }

                        if($resolverType == 'mutation'){
                            $this->mutation[$reflectorFunction->name] = $this->app->get($resolver);
                            $this->typeBuilder->addMutation($reflectorFunction->name, $docs['args'], $docs['type'], $docs['desc']);
                        }
                    }
                }
            }
        }
    }

    public function getResolvers()
    {
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
    public function buildSchema()
    {
        $schema = Cache::rememberForever(self::CACHE_SCHEMAS, function(){
            $this->buildDefaultSchema();
            $this->buildUserDefineSchema();
            return $this->schema;
        });

        return \GraphQL\Utils\BuildSchema::build($schema);
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
     * Build Resolver
     *
     * @return void
     */
    public function buildResolvers(Request $request)
    {
        try {
            $resolvers = $this->getResolvers();
        } catch (GraphQLException $e) {
            \GraphQL\Error\FormattedError::setInternalErrorMessage($e->getMessage());
            throw $e;
        }

        $contexts = [
            $this->contextToken
        ];

        $graphqlContext = $this->graphqlContext;
        Executor::setDefaultFieldResolver(function ($source, $args, $context, ResolveInfo $info)
        use ($resolvers, $request, $contexts, $graphqlContext) {
            try {

                $fieldName = $info->fieldName;

                if (is_null($fieldName)) {
                    throw new \Exception('Could not get $fieldName from ResolveInfo');
                }

                if (is_null($info->parentType)) {
                    throw new \Exception('Could not get $parentType from ResolveInfo');
                }

                $parentTypeName = $info->parentType->name;

                foreach ($contexts as $c) {
                    $c->make($request, $graphqlContext);
                }

                if (isset($resolvers[$parentTypeName])) {
                    $resolver = $resolvers[$parentTypeName];

                    if (is_array($resolver)) {
                        if (array_key_exists($fieldName, $resolver)) {
                            $value = $resolver[$fieldName];

                            $value->setSource($source);
                            $value->setRequest($request);
                            $value->setContext($graphqlContext);
                            $value->setInfo($info);

                            $argsValues = array_values($args);
                            $resolverValue = $value->$fieldName(...$argsValues);

                            if ($resolverValue instanceof AbstractViewModel) {
                                return $resolverValue->toArray();
                            } elseif ($resolverValue instanceof AbstractCollection) {
                                return $resolverValue->finalProcceed();
                            } else {
                                return null;
                            }
                        }
                    }
                }

                return Executor::defaultFieldResolver($source, $args, $context, $info);
            } catch (GraphQLException $e) {
                \GraphQL\Error\FormattedError::setInternalErrorMessage($e->getMessage());
                throw $e;
            }
        });
    }
}
