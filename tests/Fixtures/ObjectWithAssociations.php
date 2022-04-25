<?php

namespace SergiX44\Hydrator\Tests\Fixtures;

use SergiX44\Hydrator\Annotation\ArrayType;

final class ObjectWithAssociations
{
    #[ArrayType(ObjectWithString::class)]
    public array $value;
}
