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
 *   @Attribute("class", type="class-string", required=true),
 * })
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class ArrayType
{

    /**
     * The attribute value
     *
     * @var class-string
     */
    public string $class;

    /**
     * Constructor of the class
     *
     * @param class-string $class
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
        $class = new ReflectionClass($this->class);

        if ($class->getConstructor()?->getNumberOfRequiredParameters() > 0) {
            return $class->newInstanceWithoutConstructor();
        }

        return $class->newInstance();
    }
}
