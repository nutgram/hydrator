<?php



namespace SergiX44\Hydrator\Tests\Fixtures\Store;

final class Product
{
    public readonly string $name;
    public readonly Category $category;
    public readonly TagCollection $tags;
    public readonly Status $status;
}
