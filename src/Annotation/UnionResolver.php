<?php

namespace SergiX44\Hydrator\Annotation;

use Attribute;
use ReflectionException;
use ReflectionNamedType;
use ReflectionType;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
abstract class UnionResolver
{
    /**
     * @param  string  $propertyName
     * @param  ReflectionNamedType[]  $propertyTypes
     * @param  array  $data
     *
     * @return ReflectionType
     * @throws ReflectionException
     */
    abstract public function resolve(string $propertyName, array $propertyTypes, array $data): ReflectionType;
}
