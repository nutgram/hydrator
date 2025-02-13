<?php

namespace SergiX44\Hydrator\Annotation;

use Attribute;
use ReflectionClass;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
abstract class ConcreteResolver
{
    protected array $concretes = [];

    /**
     * @param array $data
     * @param ReflectionClass $class
     * @return string|null
     */
    abstract public function concreteFor(array $data, ReflectionClass $class): ?string;

    /**
     * @return array
     */
    public function getConcretes(): array
    {
        return array_values($this->concretes);
    }
}
