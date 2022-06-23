<?php

namespace SergiX44\Hydrator\Tests\Fixtures\Resolver;

use Attribute;
use SergiX44\Hydrator\Annotation\ConcreteResolver;
use SergiX44\Hydrator\Tests\Fixtures\Store\AppleJack;
use SergiX44\Hydrator\Tests\Fixtures\Store\AppleSauce;

#[Attribute(Attribute::TARGET_CLASS)]
class AppleResolver extends ConcreteResolver
{
    public function getConcreteClass(array $data): string
    {
        return match ($data['type']) {
            'jack'  => AppleJack::class,
            'sauce' => AppleSauce::class,
            default => throw new Exception('Invalid apple type'),
        };
    }
}
