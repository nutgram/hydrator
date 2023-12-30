<?php

namespace SergiX44\Hydrator\Tests\Fixtures;

use SergiX44\Hydrator\Annotation\ArrayType;
use SergiX44\Hydrator\Tests\Fixtures\Store\Apple;

final class ObjectWithArrayOfAbstracts
{
    #[ArrayType(Apple::class)]
    public array $value;
}
