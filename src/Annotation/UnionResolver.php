<?php

namespace SergiX44\Hydrator\Annotation;

use Attribute;
use ReflectionException;
use ReflectionNamedType;
use ReflectionType;
use RuntimeException;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class UnionResolver
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
    public function resolve(string $propertyName, array $propertyTypes, array $data): ReflectionType
    {
        throw new RuntimeException('This class is meant to be extended to provide your own UnionResolver logic.');
    }
}
