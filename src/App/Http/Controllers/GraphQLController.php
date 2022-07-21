<?php

namespace LaravelGraphQL\App\Http\Controllers;

use GraphQL\GraphQL as GraphQLGraphQL;
use Illuminate\Routing\Controller;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LaravelGraphQL\GraphQL;

class GraphQLController extends Controller
{
    /**
     *
     * @param GraphQL $graphQL
     */
    protected GraphQL $graphQL;

    public function __construct(
        GraphQL $graphQL
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
        $this->graphQL->buildResolvers();
        $schema = $this->graphQL->buildSchema();
        
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        $query = $input['query'];
        $variableValues = isset($input['variables']) ? $input['variables'] : null;

        try {
            $rootValue = ['prefix' => 'You said: '];
            $result = GraphQLGraphQL::executeQuery($schema, $query, $rootValue, null, $variableValues);
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
