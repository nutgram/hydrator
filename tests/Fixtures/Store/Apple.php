<?php

namespace SergiX44\Hydrator\Tests\Fixtures\Store;

use Exception;
use SergiX44\Hydrator\AbstractClassResolver;

abstract class Apple implements AbstractClassResolver
{
    public string $type;

    public static function resolveAbstractClass(array $data): string
    {
        return match ($data['type']) {
            'jack' => AppleJack::class,
            'sauce' => AppleSauce::class,
            default => throw new Exception('Invalid apple type'),
        };
    }
}
