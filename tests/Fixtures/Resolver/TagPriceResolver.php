<?php

namespace SergiX44\Hydrator\Tests\Fixtures\Resolver;

use ReflectionType;
use ReflectionUnionType;
use SergiX44\Hydrator\UnionTypeResolver;

class TagPriceResolver implements UnionTypeResolver
{

    public function resolve(ReflectionUnionType $type, array $data): ReflectionType
    {
        [$tag, $tagPrice] = $type->getTypes();

        if (isset($data['price'])) {
            return $tagPrice;
        }

        return $tag;
    }
}
