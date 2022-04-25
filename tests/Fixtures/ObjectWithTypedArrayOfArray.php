<?php

namespace SergiX44\Hydrator\Tests\Fixtures;

use SergiX44\Hydrator\Annotation\ArrayType;
use SergiX44\Hydrator\Tests\Fixtures\Store\Tag;

final class ObjectWithTypedArrayOfArray
{
    #[ArrayType(Tag::class, depth: 2)]
    public array $value;
}
