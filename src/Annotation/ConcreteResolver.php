<?php

namespace SergiX44\Hydrator\Annotation;

use Attribute;

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
     *
     * @return string|null
     */
    abstract public function concreteFor(array $data): ?string;

    /**
     * @return array
     */
    public function getConcretes(): array
    {
        return array_values($this->concretes);
    }
}
