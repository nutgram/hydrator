<?php

namespace SergiX44\Hydrator\Tests\Fixtures;

use SergiX44\Hydrator\Resolver\EnumOrScalar;

final class ObjectWithStringableEnumUnion
{
    #[EnumOrScalar]
    public readonly StringableEnum|string|int|float $value;
}
