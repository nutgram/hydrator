<?php

namespace SergiX44\Hydrator\Annotation;

use Attribute;
use ReflectionType;
use ReflectionUnionType;
use SergiX44\Hydrator\UnionTypeResolver;

/**
 * @Annotation
 *
 * @Target({"PROPERTY"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
abstract class UnionResolver
{
    /**
     * @param  ReflectionUnionType  $type
     * @param  array  $data
     * @return ReflectionType
     * @throws \ReflectionException
     */
    abstract public function resolve(ReflectionUnionType $type, array $data): ReflectionType;
}
