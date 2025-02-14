<?php

namespace SergiX44\Hydrator\Tests\Fixtures;

use SergiX44\Hydrator\Resolver\DefaultType;
use SergiX44\Hydrator\Tests\Fixtures\Store\RottenApple;

final class ObjectWithDefaultTypeResolver
{
    #[DefaultType('string')]
    public RottenApple|string|null $value = null;
}
