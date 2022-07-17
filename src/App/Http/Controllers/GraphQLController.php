<?php

namespace LaravelGraphQL\App\Http\Controllers;

use Illuminate\Routing\Controller;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LaravelGraphQL\System\Support\Facades\GraphQL as FacadesGraphQL;

class GraphQLController extends Controller
{
    /**
     *
     * @param FacadesGraphQL $graphQL
     */
    protected FacadesGraphQL $graphQL;

    public function __construct(
        FacadesGraphQL $graphQL
    )
    {
        $this->graphQL = $graphQL;  
    }

    /**
     * /graphql
     *
     * @return void
     */
    public function index()
    {
        $this->graphQL->buildResolver();
        $queryType = new ObjectType([
            'name' => 'Query',
            'fields' => [
                'echo' => [
                    'type' => Type::string(),
                    'args' => [
                        'message' => Type::nonNull(Type::string()),
                    ],
                    'resolve' => fn ($rootValue, array $args): string => $rootValue['prefix'] . $args['message'],
                ],
            ],
        ]);

        $schema = new Schema([
            'query' => $queryType
        ]);
        
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        $query = $input['query'];
        $variableValues = isset($input['variables']) ? $input['variables'] : null;

        try {
            $rootValue = ['prefix' => 'You said: '];
            $result = GraphQL::executeQuery($schema, $query, $rootValue, null, $variableValues);
            $output = $result->toArray();
        } catch (\Exception $e) {
            $output = [
                'errors' => [
                    [
                        'message' => $e->getMessage()
                    ]
                ]
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($output);
    }
}
