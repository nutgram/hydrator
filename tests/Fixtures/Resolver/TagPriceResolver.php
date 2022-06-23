<?php

namespace SergiX44\Hydrator\Tests\Fixtures\Resolver;

use Attribute;
use ReflectionType;
use ReflectionUnionType;
use SergiX44\Hydrator\Annotation\UnionResolver;

#[Attribute(Attribute::TARGET_PROPERTY)]
class TagPriceResolver extends UnionResolver
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
