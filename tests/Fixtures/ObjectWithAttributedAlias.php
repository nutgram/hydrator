<?php

namespace SergiX44\Hydrator\Tests\Fixtures;

use SergiX44\Hydrator\Annotation\Alias;

final class ObjectWithAttributedAlias
{
    #[Alias('non-normalized-value')]
    public string $value;
}
