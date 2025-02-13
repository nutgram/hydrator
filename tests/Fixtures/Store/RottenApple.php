<?php

namespace SergiX44\Hydrator\Tests\Fixtures\Store;

use SergiX44\Hydrator\Resolver\AnonymousResolver;

#[AnonymousResolver]
abstract class RottenApple
{
    public string $type;
}
