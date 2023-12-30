<?php

namespace SergiX44\Hydrator\Annotation;

use Attribute;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 * @NamedArgumentConstructor
 *
 * @Attributes({
 *
 *   @Attribute("value", type="string", required=true),
 * })
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Alias
{
    /**
     * The attribute value.
     *
     * @var string
     */
    public string $value;

    /**
     * Constructor of the class.
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
