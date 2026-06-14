<?php

namespace SergiX44\Hydrator\Tests\Fixtures;

use SergiX44\Hydrator\Annotation\ArrayType;
use SergiX44\Hydrator\Tests\Fixtures\Store\Label;

final class ObjectWithLabels
{
    /**
     * @var Label[]
     */
    #[ArrayType(Label::class)]
    public array $labels;
}
