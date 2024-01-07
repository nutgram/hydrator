<?php

namespace SergiX44\Hydrator\Annotation;

use Attribute;
use InvalidArgumentException;
use SergiX44\Hydrator\Mutation\Mutator;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Mutate
{
    /**
     * The attribute value.
     *
     * @var class-string<Mutator>[]
     */
    public array $mutators;

    /**
     * Constructor of the class.
     *
     * @param string ...$mutators
     */
    public function __construct(string ...$mutators)
    {
        foreach ($mutators as $mutator) {
            if (!is_subclass_of($mutator, Mutator::class)) {
                throw new InvalidArgumentException(sprintf('Class %s must implements %s', $mutator, Mutator::class));
            }
        }

        $this->mutators = $mutators;
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public function apply($value): mixed
    {
        foreach ($this->mutators as $mutator) {
            $value = (new $mutator())->mutate($value);
        }

        return $value;
    }
}
