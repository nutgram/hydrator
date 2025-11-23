<?php

namespace SergiX44\Hydrator\Annotation;

use Attribute;
use RuntimeException;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ConcreteResolver
{
    protected array $concretes = [];

    /**
     * @param array $data
     * @param array $all
     *
     * @return string|null
     */
    public function concreteFor(array $data, array $all): ?string
    {
        throw new RuntimeException('This class is meant to be extended to provide your own ConcreteResolver logic.');
    }

    /**
     * @return array
     */
    public function getConcretes(): array
    {
        return array_values($this->concretes);
    }
}
