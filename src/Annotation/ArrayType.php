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
     * When true, scalar (and null) array elements are passed through as-is
     * instead of being hydrated into the target class.
     *
     * @var bool
     */
    public bool $skipScalars;

    /**
     * Constructor of the class.
     *
     * @param class-string $class
     */
    public function __construct(string $class, int $depth = 1, bool $skipScalars = false)
    {
        $this->class = $class;
        $this->depth = $depth;
        $this->skipScalars = $skipScalars;
    }
}
