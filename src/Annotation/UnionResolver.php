<?php


namespace SergiX44\Hydrator\Annotation;

use Attribute;

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
final class UnionResolver
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
}
