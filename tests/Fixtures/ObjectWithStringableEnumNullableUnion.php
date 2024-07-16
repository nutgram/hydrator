<?php

namespace SergiX44\Hydrator\Tests\Fixtures;

use SergiX44\Hydrator\Resolver\EnumOrScalar;

final class ObjectWithStringableEnumNullableUnion
{
    #[EnumOrScalar]
    public StringableEnum|string|null $value = null;
}
