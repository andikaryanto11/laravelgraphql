<?php

namespace LaravelGraphQL\Types;

use LaravelCommon\Responses\PagedJsonResponse;
use LaravelCommon\ViewModels\AbstractCollection;
use LaravelCommon\ViewModels\PaggedCollection;

class GraphQLCollection
{

    public static function of(string $type): string
    {
        return "\ntype GraphQLCollection$type {
    list: [$type]
    paging: Paging
}\n";
    }

    public function buildList(PaggedCollection $collection)
    {
        return [
            'list' => $collection->finalProcceed(),
            'paging' => [
                'page' => $collection->getPage(),
                'size' => $collection->getSize()
            ]
        ];
    }
}
