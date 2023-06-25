<?php

namespace SergiX44\Hydrator\Tests\Fixtures;

final class ObjectWithEnumInConstructor
{
    public StringableEnum $value;

    public function __construct(StringableEnum $value = StringableEnum::foo)
    {
        $this->value = $value;
    }
}
