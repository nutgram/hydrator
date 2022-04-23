<?php


namespace SergiX44\Hydrator\Annotation;

/**
 * Import classes
 */

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
 *   @Attribute("value", type="string", required=true),
 * })
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class ArrayType
{

    /**
     * The attribute value
     *
     * @var string
     */
    public string $class;

    /**
     * Constructor of the class
     *
     * @param string $class
     */
    public function __construct(string $class)
    {
        $this->class = $class;
    }

    /**
     * @throws \ReflectionException
     */
    public function getInstance()
    {
        return (new ReflectionClass($this->class))->newInstance();
    }
}
