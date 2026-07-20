<?php

namespace SergiX44\Hydrator\Tests\Fixtures;

use SergiX44\Hydrator\Annotation\ArrayType;
use SergiX44\Hydrator\Tests\Fixtures\Store\Tag;

final class ObjectWithTypedArraySkippingScalars
{
    #[ArrayType(Tag::class, skipScalars: true)]
    public array $value;
}
