<?php

namespace SergiX44\Hydrator\Tests\Fixtures\Store;

use SergiX44\Hydrator\Annotation\ArrayType;

final class Product
{
    public string $name;
    public Category $category;
    #[ArrayType(Tag::class)]
    public array $tags;
    public Status $status;
}
