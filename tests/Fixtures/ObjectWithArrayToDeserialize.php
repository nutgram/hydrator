<?php

namespace SergiX44\Hydrator\Tests\Fixtures;

use SergiX44\Hydrator\Annotation\Mutate;
use SergiX44\Hydrator\Mutation\JsonDecodeObject;

final class ObjectWithArrayToDeserialize
{
    public string $name;

    #[Mutate(JsonDecodeObject::class)]
    public array $value;
}
