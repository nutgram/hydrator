<?php

namespace SergiX44\Hydrator\Tests\Fixtures\DI;

use SergiX44\Hydrator\Annotation\ArrayType;

class Forest
{
    #[ArrayType(Tree::class)]
    public array $trees;
}
