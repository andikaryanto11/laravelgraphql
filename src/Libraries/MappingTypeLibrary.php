<?php

namespace LaravelGraphQL\Libraries;

class MappingTypeLibrary {

    protected static array $mappedPrimitiveType = [
        'String' => 'string',
        'Int' => 'int'
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