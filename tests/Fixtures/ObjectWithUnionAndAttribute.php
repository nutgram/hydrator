<?php

namespace SergiX44\Hydrator\Tests\Fixtures;

use SergiX44\Hydrator\Tests\Fixtures\Resolver\TagPriceResolver;
use SergiX44\Hydrator\Tests\Fixtures\Store\Tag;
use SergiX44\Hydrator\Tests\Fixtures\Store\TagPrice;

class ObjectWithUnionAndAttribute
{
    #[TagPriceResolver]
    public Tag|TagPrice $tag;
}
