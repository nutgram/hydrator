<?php

namespace SergiX44\Hydrator\Annotation;

use Attribute;
use ReflectionClass;

/**
 * @Annotation
 *
 * @Target({"PROPERTY"})
 *
 * @NamedArgumentConstructor
 *
 * @Attributes({
 *
 *   @Attribute("class", type="class-string", required=true),
 * })
 */
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

    /**
     * @throws \ReflectionException
     */
    public function getInstance()
    {
        $class = new ReflectionClass($this->class);

        if ($class->getConstructor()?->getNumberOfRequiredParameters() > 0) {
            return $class->newInstanceWithoutConstructor();
        }

        return $class->newInstance();
    }
}
