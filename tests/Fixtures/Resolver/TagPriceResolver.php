<?php

namespace SergiX44\Hydrator\Tests\Fixtures\Resolver;

use Attribute;
use ReflectionType;
use SergiX44\Hydrator\Annotation\UnionResolver;

#[Attribute(Attribute::TARGET_PROPERTY)]
class TagPriceResolver extends UnionResolver
{
    public function resolve(string $propertyName, array $propertyTypes, array $data): ReflectionType
    {
        [$tag, $tagPrice] = $propertyTypes;

        if (isset($data['price'])) {
            return $tagPrice;
        }

        return $tag;
    }
}
