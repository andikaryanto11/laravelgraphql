<?php

namespace LaravelGraphQL\Attributes\Types;

use LaravelCommon\ViewModels\PaggedCollection;

class PagedCollectionType
{
    protected PaggedCollection $collection;
    
    public function __construct(
        PaggedCollection $paggedCollection
    )
    {
        $this->collection = $paggedCollection;
    }
    
    public static function of(string $type): string
    {
        return "\ntype PagedCollectionType$type {
    list: [$type]
    paging: Paging
}\n";
    }

    public function toArray()
    {
        return [
            'list' => $this->collection->finalProcceed(),
            'paging' => [
                'page' => $this->collection->getPage(),
                'limit' => $this->collection->getSize(),
                'total_page' => $this->collection->getTotalPage(),
                'total_data' => $this->collection->getTotalRecord()
            ]
        ];
    }
}
