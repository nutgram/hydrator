<?php

namespace SergiX44\Hydrator;

use ReflectionType;
use ReflectionUnionType;

interface UnionTypeResolver
{
    public function resolve(ReflectionUnionType $type, array $data): ReflectionType;
}
