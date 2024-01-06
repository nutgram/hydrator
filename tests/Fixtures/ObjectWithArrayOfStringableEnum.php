<?php

namespace SergiX44\Hydrator\Tests\Fixtures;

use SergiX44\Hydrator\Annotation\ArrayType;

final class ObjectWithArrayOfStringableEnum
{
    #[ArrayType(StringableEnum::class)]
    public readonly array $value;
}
