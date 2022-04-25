<?php

namespace SergiX44\Hydrator\Tests\Fixtures;

use SergiX44\Hydrator\Annotation\ArrayType;
use SergiX44\Hydrator\Tests\Fixtures\Store\Tag;

final class ObjectWithTypedArray
{
    #[ArrayType(Tag::class)]
    public array $value;
}
