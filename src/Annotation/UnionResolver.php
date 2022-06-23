<?php

namespace SergiX44\Hydrator\Annotation;

use Attribute;
use ReflectionType;
use ReflectionUnionType;

/**
 * @Annotation
 *
 * @Target({"PROPERTY"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
abstract class UnionResolver
{
    /**
     * @param ReflectionUnionType $type
     * @param array               $data
     *
     * @throws \ReflectionException
     *
     * @return ReflectionType
     */
    abstract public function resolve(ReflectionUnionType $type, array $data): ReflectionType;
}
