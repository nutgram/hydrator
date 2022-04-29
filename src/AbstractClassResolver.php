<?php

namespace SergiX44\Hydrator;

interface AbstractClassResolver
{
    /**
     * @param array $data
     * @return class-string
     */
    public static function resolveAbstractClass(array $data): string;
}
