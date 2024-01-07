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
     * @param string                $propertyName
     * @param ReflectionNamedType[] $propertyTypes
     * @param array                 $data
     *
     * @throws ReflectionException
     *
     * @return ReflectionType
     */
    abstract public function resolve(string $propertyName, array $propertyTypes, array $data): ReflectionType;
}
