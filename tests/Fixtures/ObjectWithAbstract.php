<?php

namespace SergiX44\Hydrator\Tests\Fixtures;

use SergiX44\Hydrator\Tests\Fixtures\Store\Apple;

final class ObjectWithAbstract
{
    public Apple $value;
    public string $name = 'Apple';
}
