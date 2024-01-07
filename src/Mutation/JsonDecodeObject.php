<?php

namespace SergiX44\Hydrator\Mutation;

class JsonDecodeObject implements Mutator
{
    public function mutate(mixed $value): mixed
    {
        return json_decode($value, false, 512, JSON_THROW_ON_ERROR);
    }
}
