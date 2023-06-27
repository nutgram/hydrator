<?php

namespace SergiX44\Hydrator\Tests\Fixtures;

use SergiX44\Hydrator\Annotation\ArrayType;
use SergiX44\Hydrator\Annotation\SkipConstructor;

#[SkipConstructor]
final class ObjectWithEnumInConstructor
{
    public StringableEnum $stringableEnum;

    #[ArrayType(NumerableEnum::class)]
    public array $numerableEnums;

    public function __construct(StringableEnum $value, array $numerableEnums)
    {
        $this->stringableEnum = $value;
        $this->numerableEnums = $numerableEnums;
    }
}
