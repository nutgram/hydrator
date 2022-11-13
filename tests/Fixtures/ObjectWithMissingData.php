<?php

namespace SergiX44\Hydrator\Tests\Fixtures;

use SergiX44\Hydrator\Tests\Fixtures\Store\Tag;

final class ObjectWithMissingData
{
    public string $name;

    protected ?Tag $tag = null;

    public function getTag(): ?Tag
    {
        return $this->tag;
    }
}
