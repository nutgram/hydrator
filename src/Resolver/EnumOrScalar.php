<?php

namespace SergiX44\Hydrator\Resolver;

use Attribute;
use BackedEnum;
use ReflectionType;
use SergiX44\Hydrator\Annotation\UnionResolver;
use SergiX44\Hydrator\Exception\UnsupportedPropertyTypeException;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EnumOrScalar extends UnionResolver
{
    public function resolve(string $propertyName, array $propertyTypes, array $data): ReflectionType
    {
        $enum = array_shift($propertyTypes);
        if (empty($propertyTypes)) {
            return $enum;
        }

        $enumClass = $enum->getName();

        if (!is_subclass_of($enumClass, BackedEnum::class)) {
            throw new UnsupportedPropertyTypeException(
                sprintf(
                    'The enum must be the first type of the union, %s given.',
                    $enumClass
                )
            );
        }

        $value = $data[$propertyName] ?? array_shift($data);
        if ((is_string($value) || is_int($value)) && $enumClass::tryFrom($value) !== null) {
            return $enum;
        }

        $valueType = gettype($value);
        $valueType = match ($valueType) {
            'integer' => 'int',
            'double'  => 'float',
            'boolean' => 'bool',
            'NULL'    => 'null',
            default   => $valueType,
        };

        foreach ($propertyTypes as $t) {
            if ($t->getName() === $valueType) {
                return $t;
            }
        }

        throw new UnsupportedPropertyTypeException(
            sprintf(
                'The property "%s" can only be %s or %s, %s given.',
                $propertyName,
                $enumClass,
                implode(' or ', $propertyTypes),
                $valueType
            )
        );
    }
}
