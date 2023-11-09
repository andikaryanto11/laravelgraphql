<?php

namespace LaravelGraphQL;

use Exception;
use GraphQL\Executor\Executor;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use LaravelCommon\ViewModels\AbstractCollection;
use LaravelCommon\ViewModels\AbstractViewModel;
use LaravelGraphQL\Attributes\Argument;
use LaravelGraphQL\Attributes\Description;
use LaravelGraphQL\Attributes\Middleware;
use LaravelGraphQL\Attributes\Resolver;
use LaravelGraphQL\Attributes\Type;
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
     * Undocumented function
     *
     * @param Container $app
     */
    public function __construct(
        Container $app,
        TypeBuilder $typeBuilder,
        Context $graphqlContext
    ) {
        $this->app = $app;
        $this->typeBuilder = $typeBuilder;
        $this->graphqlContext = $graphqlContext;
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
            $resolverInstance = $this->app->make($resolver);
            $reflector = (new ReflectionClass($resolver));
            $reflectorFunctions = $reflector->getMethods(ReflectionMethod::IS_FINAL);
            foreach ($reflectorFunctions as $reflectorFunction) {
                if ($reflectorFunction->name != '__construct') {

                    $attrs = $reflectorFunction->getAttributes();
                    $resolver = '';
                    $description = '';
                    $args = '';
                    $type = '';
                    $middlewares = [];
                    foreach ($attrs as $attr) {
                        $attributeArgument = $attr->getArguments();
                        $attributeName = $attr->getName();
                        if ($attributeName == Argument::class) {
                            $args =  $this->typeBuilder->buildArgument($attributeArgument[0]);
                        }

                        if ($attributeName == Description::class) {
                            $description =  $attributeArgument[0];
                        }

                        if ($attributeName == Type::class) {
                            $type =   $this->typeBuilder->buildType($attributeArgument[0]);
                        }

                        if ($attributeName == Resolver::class) {
                            $resolver =  $attributeArgument[0];
                        }

                        if ($attributeName == Middleware::class) {
                            $middlewares =  $attributeArgument[0];
                        }
                    }

                    if (!empty($attrs)) {
                        if ($resolver == Resolver::QUERY) {
                            $this->queries[$reflectorFunction->name] = $this->createResolver($resolverInstance,  $middlewares);
                            $this->typeBuilder->addQuery($reflectorFunction->name,  $args,  $type, $description);
                        }

                        if ($resolver == Resolver::MUTATION) {
                            $this->mutation[$reflectorFunction->name] = $this->createResolver($resolverInstance,  $middlewares);
                            $this->typeBuilder->addMutation($reflectorFunction->name,  $args,  $type, $description);
                        }
                    }
                }
            }
        }
    }

    /**
     * Create resolver and its middleware
     *
     * @param mixed $resolver
     * @param array $middlewares
     * @return void
     */
    private function createResolver($resolver, $middlewares = [])
    {

        $instanceMiddlwares = [];
        foreach ($middlewares as $middleware) {
            $graphQlMiddleware = new GraphQLMiddleware();
            $middlewareParts = explode(':', $middleware);
            if (count($middlewareParts) == 2) {
                $scopes = explode(',', $middlewareParts[1]);
                $graphQlMiddleware->setScope($scopes);
            }
            $graphQlMiddleware->setMiddleware($this->app->make($middlewareParts[0]));
            $instanceMiddlwares[] = $graphQlMiddleware;
        }

        return [
            'resolver' => $resolver,
            'middlewares' => $instanceMiddlwares
        ];
    }

    /**
     * get All Resolvers
     *
     * @return void
     */
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
        $schema = Cache::rememberForever(self::CACHE_SCHEMAS, function () {
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
        $this->schema .= "\n" . file_get_contents(__DIR__ . '/schema.graphqls');
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

                if (isset($resolvers[$parentTypeName])) {
                    $resolver = $resolvers[$parentTypeName];

                    if (is_array($resolver)) {
                        if (array_key_exists($fieldName, $resolver)) {


                            $resolverInstance = $resolver[$fieldName]['resolver'];
                            $resolverMiddlewares = $resolver[$fieldName]['middlewares'];

                            foreach ($resolverMiddlewares as $middleware) {
                                try {
                                    $result = $middleware->getMiddleware()->handle($request, function ($request) {
                                        return null;
                                    }, $middleware->getScope());

                                    if (!is_null($result)) {
                                        throw new GraphQLException($result->getMessage());
                                    }
                                } catch (Exception $e) {
                                    throw new GraphQLException($e->getMessage());
                                }
                            }

                            $resolverInstance->setSource($source);
                            $resolverInstance->setRequest($request);
                            $resolverInstance->setContext($graphqlContext);
                            $resolverInstance->setInfo($info);

                            $argsValues = array_values($args);
                            $resolverValue = $resolverInstance->$fieldName(...$argsValues);

                            if ($resolverValue instanceof AbstractViewModel) {
                                return $resolverValue->finalArray();
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
