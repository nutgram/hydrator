<?php

namespace SergiX44\Hydrator\Tests\Fixtures\Store;

use SergiX44\Hydrator\Annotation\OverrideConstructor;

#[OverrideConstructor('putKey')]
class Audi extends Car
{
    public function __construct(public string $model, public int $year)
    {
    }
}
