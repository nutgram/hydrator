<?php

namespace SergiX44\Hydrator\Tests\Fixtures\Resolver;

use Attribute;
use SergiX44\Hydrator\Annotation\ConcreteResolver;
use SergiX44\Hydrator\Tests\Fixtures\Store\AppleJack;
use SergiX44\Hydrator\Tests\Fixtures\Store\AppleSauce;

#[Attribute(Attribute::TARGET_CLASS)]
class AppleResolver extends ConcreteResolver
{
    protected array $concretes = [
        'jack'  => AppleJack::class,
        'sauce' => AppleSauce::class,
    ];

    public function concreteFor(array $data): ?string
    {
        return $this->concretes[$data['type']] ?? null;
    }
}
