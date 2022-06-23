<?php

namespace SergiX44\Hydrator\Annotation;

use Attribute;

/**
 * @Annotation
 *
 * @Target({"PROPERTY"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
abstract class ConcreteResolver
{
    /**
     * @param  array  $data
     * @return string
     */
    abstract public function getConcreteClass(array $data): string;
}