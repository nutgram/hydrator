<?php

namespace SergiX44\Hydrator\Tests\Fixtures\Store;

use SergiX44\Hydrator\Resolver\ResolveToAnonymous;

#[ResolveToAnonymous]
abstract class RottenApple
{
    public string $type;
}
