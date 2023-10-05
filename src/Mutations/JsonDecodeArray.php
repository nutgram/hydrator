<?php

namespace SergiX44\Hydrator\Mutations;

use SergiX44\Hydrator\Mutator;

class JsonDecodeArray implements Mutator
{
    public function mutate(mixed $value): mixed
    {
        return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    }
}
