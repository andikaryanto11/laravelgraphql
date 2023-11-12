<?php

namespace LaravelGraphQL\Libraries;

use GraphQL\Type\Definition\Type;

class MappingTypeLibrary {

    protected static array $mappedPrimitiveType = [
        Type::STRING => 'string',
        Type::INT => 'int',
        Type::FLOAT => 'float'
    ];

    public static function getPrimitiveTypeGraphQL(string $type): string|null
    {
        foreach (self::$mappedPrimitiveType as $graphq1Type => $primitiveType) {
            if ($type == $primitiveType) {
                return $graphq1Type;
            }
        }

        return null;
    }
} 