<?php

namespace SergiX44\Hydrator\Tests\Fixtures\Store;

use SergiX44\Hydrator\Tests\Fixtures\Resolver\AppleResolver;

#[AppleResolver]
abstract class Apple
{
    public string $type;
}
