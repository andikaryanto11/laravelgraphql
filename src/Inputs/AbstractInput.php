<?php

namespace LaravelGraphQL\Inputs;

use LaravelGraphQL\Libraries\MappingTypeLibrary;
use ReflectionClass;
use ReflectionProperty;

abstract class AbstractInput
{
    public function getInput()
    {

        $reflectorProperties = $this->getProperties();
        $input = '';

        foreach ($reflectorProperties as $reflectorProperty) {
            $propName = $reflectorProperty->getName();

            $typeName = $reflectorProperty->getType()->getName();

            $propValue = null;

            if ($typeName == 'string') {
                $propValue = '"' . $this->$propName . '"';
            } else {
                $propValue = $this->$propName;
            }

            $input .= $propName . ':' . $propValue . ' ';
        }


        return $this->getClassName() . " = { $input }";
    }

    private function getClassName()
    {

        $namespaces = explode('\\', static::class);
        return $namespaces[count($namespaces) - 1];
    }

    private function getProperties()
    {
        $reflectoionClass = (new ReflectionClass(static::class));
        $reflectorProperties = $reflectoionClass->getProperties(ReflectionProperty::IS_PROTECTED);
        return $reflectorProperties;
    }

    public function generateInput()
    {
        $reflectorProperties = $this->getProperties();

        $graphQLProps = [];

        foreach ($reflectorProperties as $reflectorProperty) {
            $propName = $reflectorProperty->getName();

            $typeName = $reflectorProperty->getType()->getName();

            $graphQLType = MappingTypeLibrary::getPrimitiveTypeGraphQL($typeName);
            $graphQLProps[] = "$propName: $graphQLType!";
        }
        $className = $this->getClassName();
        $graphQLProp = implode("\n\t", $graphQLProps);

        return "\ninput $className {
    $graphQLProp
}\n";
    }

    public function parseFromArray(array $values)
    {
        foreach ($values as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }
}
