<?php

namespace SergiX44\Hydrator\Resolver;

use Attribute;
use ReflectionType;
use SergiX44\Hydrator\Annotation\UnionResolver;
use SergiX44\Hydrator\Exception\UnsupportedPropertyTypeException;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DefaultType extends UnionResolver
{
    /**
     * @var class-string|string
     */
    public string $type;

    /**
     * @param class-string|string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function resolve(string $propertyName, array $propertyTypes, array $data): ReflectionType
    {
        foreach ($propertyTypes as $type) {
            if ($type->getName() === $this->type) {
                return $type;
            }
        }

        throw new UnsupportedPropertyTypeException(sprintf(
            'The property "%s" can only be %s, %s given.',
            $propertyName,
            implode(' or ', $propertyTypes),
            $this->type,
        ));
    }
}
