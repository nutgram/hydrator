<?php

namespace SergiX44\Hydrator\Resolver;

use Attribute;
use BackedEnum;
use ReflectionType;
use ReflectionUnionType;
use SergiX44\Hydrator\Annotation\UnionResolver;
use SergiX44\Hydrator\Exception\UnsupportedPropertyTypeException;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EnumOrScalar extends UnionResolver
{
    public function resolve(ReflectionUnionType $type, array $data): ReflectionType
    {
        $types = $type->getTypes();
        $enum = array_shift($types);

        if (empty($types)) {
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

        $value = array_shift($data);
        if ((is_string($value) || is_int($value)) && $enumClass::tryFrom($value) !== null) {
            return $enum;
        }

        $valueType = gettype($value);
        $valueType = match ($valueType) {
            'integer' => 'int',
            'double' => 'float',
            'boolean' => 'bool',
            default => $valueType,
        };

        foreach ($types as $t) {
            if ($t->getName() === $valueType) {
                return $t;
            }
        }

        throw new UnsupportedPropertyTypeException(
            sprintf(
                'This property can be %s or %s, %s given.',
                $enumClass,
                implode(' or ', $types),
                $valueType
            )
        );
    }
}
