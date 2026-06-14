<?php

namespace SergiX44\Hydrator\Tests\Fixtures\Resolver;

use Attribute;
use SergiX44\Hydrator\Annotation\ConcreteResolver;
use SergiX44\Hydrator\Tests\Fixtures\Store\BigLabel;
use SergiX44\Hydrator\Tests\Fixtures\Store\SmallLabel;

#[Attribute(Attribute::TARGET_CLASS)]
class LabelResolver extends ConcreteResolver
{
    protected array $concretes = [
        'big'   => BigLabel::class,
        'small' => SmallLabel::class,
    ];

    public function concreteFor(array $data, array $all): ?string
    {
        return $this->concretes[$data['type']] ?? null;
    }
}
