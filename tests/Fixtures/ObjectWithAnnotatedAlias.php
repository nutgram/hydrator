<?php

namespace SergiX44\Hydrator\Tests\Fixtures;

final class ObjectWithAnnotatedAlias
{
    /**
     * @Alias("non-normalized-value")
     */
    public string $value;
}
