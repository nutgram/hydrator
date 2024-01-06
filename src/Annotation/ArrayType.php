<?php

namespace SergiX44\Hydrator\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class ArrayType
{
    /**
     * The attribute value.
     *
     * @var class-string
     */
    public string $class;

    /**
     * @var int
     */
    public int $depth;

    /**
     * Constructor of the class.
     *
     * @param class-string $class
     */
    public function __construct(string $class, int $depth = 1)
    {
        $this->class = $class;
        $this->depth = $depth;
    }
}
