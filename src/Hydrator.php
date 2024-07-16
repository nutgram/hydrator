<?php

namespace SergiX44\Hydrator;

use BackedEnum;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionEnum;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use SergiX44\Hydrator\Annotation\Alias;
use SergiX44\Hydrator\Annotation\ArrayType;
use SergiX44\Hydrator\Annotation\ConcreteResolver;
use SergiX44\Hydrator\Annotation\Mutate;
use SergiX44\Hydrator\Annotation\SkipConstructor;
use SergiX44\Hydrator\Annotation\UnionResolver;
use SergiX44\Hydrator\Exception\InvalidObjectException;

use function array_key_exists;
use function class_exists;
use function ctype_digit;
use function filter_var;
use function get_object_vars;
use function implode;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_string;
use function is_subclass_of;
use function sprintf;
use function strtotime;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;
use const FILTER_VALIDATE_FLOAT;
use const FILTER_VALIDATE_INT;

class Hydrator implements HydratorInterface
{
    protected ?ContainerInterface $container = null;

    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Hydrates the given object with the given data.
     *
     * @param class-string<T>|T $object
     * @param array|object      $data
     *
     * @throws Exception\UnsupportedPropertyTypeException
     *                                                    If one of the object properties contains an unsupported type.
     * @throws Exception\MissingRequiredValueException
     *                                                    If the given data doesn't contain required value.
     * @throws Exception\InvalidValueException
     *                                                    If the given data contains an invalid value.
     * @throws Exception\HydrationException
     *                                                    If the object cannot be hydrated.
     * @throws InvalidArgumentException
     *                                                    If the data isn't valid.
     * @throws Exception\UntypedPropertyException
     *                                                    If one of the object properties isn't typed.
     *
     * @return T
     *
     * @template T
     */
    public function hydrate(string|object $object, array|object $data): object
    {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        $object = $this->initializeObject($object, $data);

        $class = new ReflectionClass($object);
        foreach ($class->getProperties() as $property) {
            // statical properties cannot be hydrated...
            if ($property->isStatic()) {
                continue;
            }
            $propertyType = $property->getType();

            if ($propertyType === null) {
                throw new Exception\UntypedPropertyException(sprintf(
                    'The %s.%s property is not typed.',
                    $class->getShortName(),
                    $property->getName()
                ));
            }

            $key = $property->getName();
            if (!array_key_exists($key, $data)) {
                $alias = $this->getAttributeInstance($property, Alias::class);
                if (isset($alias)) {
                    $key = $alias->value;
                }
            }

            if ($propertyType instanceof ReflectionUnionType) {
                $resolver = $this->getAttributeInstance(
                    $property,
                    UnionResolver::class,
                    ReflectionAttribute::IS_INSTANCEOF
                );
                if (isset($resolver)) {
                    $propertyType = $resolver->resolve(
                        $key,
                        $propertyType->getTypes(),
                        isset($data[$key]) && is_array($data[$key]) ? $data[$key] : $data
                    );
                } else {
                    throw new Exception\UnsupportedPropertyTypeException(sprintf(
                        'The %s.%s property cannot be hydrated automatically. Please define an union type resolver attribute or remove the union type.',
                        $class->getShortName(),
                        $property->getName()
                    ));
                }
            }

            if (!array_key_exists($key, $data)) {
                if (!$property->isInitialized($object)) {
                    throw new Exception\MissingRequiredValueException($property, sprintf(
                        'The %s.%s property is required.',
                        $class->getShortName(),
                        $property->getName()
                    ));
                }

                continue;
            }

            $mutator = $this->getAttributeInstance($property, Mutate::class);
            if ($mutator !== null) {
                $data[$key] = $mutator->apply($data[$key]);
            }

            $this->hydrateProperty($object, $class, $property, $propertyType, $data[$key]);
            unset($data[$key]);
        }

        // if the object has a __set method, we will use it to hydrate the remaining data
        if (!empty($data) && $class->hasMethod('__set')) {
            foreach ($data as $key => $value) {
                $object->$key = $value;
            }
        }

        return $object;
    }

    /**
     * Hydrates the given object with the given JSON.
     *
     * @param class-string<T>|T $object
     * @param string            $json
     * @param ?int              $flags
     *
     * @throws InvalidArgumentException
     *                                      If the JSON cannot be decoded.
     * @throws Exception\HydrationException
     *                                      If the object cannot be hydrated.
     *
     * @return T
     *
     * @template T
     */
    public function hydrateWithJson(string|object $object, string $json, ?int $flags = null): object
    {
        if (null === $flags) {
            $flags = JSON_OBJECT_AS_ARRAY;
        }

        json_decode('');
        $data = json_decode($json, null, 512, $flags);
        if (JSON_ERROR_NONE != json_last_error()) {
            throw new InvalidArgumentException(sprintf(
                'Unable to decode JSON: %s',
                json_last_error_msg()
            ));
        }

        return $this->hydrate($object, $data);
    }

    /**
     * @param class-string|object $object
     *
     * @throws \ReflectionException
     *
     * @return object|null
     */
    public function getConcreteResolverFor(string|object $object): ?ConcreteResolver
    {
        return $this->getAttributeInstance(
            new ReflectionClass($object),
            ConcreteResolver::class,
            ReflectionAttribute::IS_INSTANCEOF
        );
    }

    /**
     * Initializes the given object.
     *
     * @param class-string<T>|T $object
     *
     * @throws ContainerExceptionInterface
     *                                     If the object cannot be initialized.
     * @throws InvalidArgumentException
     *
     * @return T
     *
     * @template T
     */
    private function initializeObject(string|object $object, array|object $data): object
    {
        if (is_object($object)) {
            return $object;
        }

        if (!is_string($object) || !class_exists($object)) {
            throw new InvalidArgumentException(sprintf(
                'The %s::hydrate() method expects an object or name of an existing class.',
                __CLASS__
            ));
        }

        $reflectionClass = new ReflectionClass($object);

        if ($reflectionClass->isAbstract()) {
            $attribute = $this->getAttributeInstance(
                $reflectionClass,
                ConcreteResolver::class,
                ReflectionAttribute::IS_INSTANCEOF
            );

            if ($attribute === null) {
                throw new InvalidObjectException(sprintf(
                    'The %s class cannot be instantiated. Please define a concrete resolver attribute.',
                    $object
                ));
            }

            if (is_object($data)) {
                $data = get_object_vars($data);
            }

            return $this->initializeObject($attribute->concreteFor($data), $data);
        }

        // if we have a container, get the instance through it
        $skipConstructor = $this->getAttributeInstance($reflectionClass, SkipConstructor::class);
        if ($skipConstructor !== null) {
            return $reflectionClass->newInstanceWithoutConstructor();
        }

        if ($this->container !== null) {
            return $this->container->get($object);
        }

        $constructor = $reflectionClass->getConstructor();
        if (isset($constructor) && $constructor->getNumberOfRequiredParameters() > 0) {
            throw new InvalidArgumentException(sprintf(
                'The %s object cannot be hydrated because its constructor has required parameters.',
                $reflectionClass->getName()
            ));
        }

        /** @var T */
        return $reflectionClass->newInstance();
    }

    /**
     * Gets an alias for the given property.
     *
     * @template T
     *
     * @param ReflectionProperty|ReflectionClass $target
     * @param class-string<T>                    $class
     * @param int                                $criteria
     *
     * @return T|null
     */
    private function getAttributeInstance(
        ReflectionProperty|ReflectionClass $target,
        string $class,
        int $criteria = 0
    ): mixed {
        $attributes = $target->getAttributes($class, $criteria);
        if (isset($attributes[0])) {
            return $attributes[0]->newInstance();
        }

        return null;
    }

    /**
     * Hydrates the given property with the given value.
     *
     * @param object              $object
     * @param ReflectionClass     $class
     * @param ReflectionProperty  $property
     * @param ReflectionNamedType $type
     * @param mixed               $value
     *
     * @throws Exception\UnsupportedPropertyTypeException
     *                                                    If the given property contains an unsupported type.
     * @throws Exception\InvalidValueException
     *                                                    If the given value isn't valid.
     *
     * @return void
     */
    private function hydrateProperty(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        mixed $value
    ): void {
        $propertyType = $type->getName();

        match (true) {
            // an empty string for a non-string type is always processes as null
            '' === $value && 'string' !== $propertyType, null === $value => $this->propertyNull(
                $object,
                $class,
                $property,
                $type
            ),

            'bool' === $propertyType => $this->propertyBool($object, $class, $property, $type, $value),

            'int' === $propertyType => $this->propertyInt($object, $class, $property, $type, $value),

            'float' === $propertyType => $this->propertyFloat($object, $class, $property, $type, $value),

            'string' === $propertyType => $this->propertyString($object, $class, $property, $type, $value),

            'array' === $propertyType => $this->propertyArray($object, $class, $property, $type, $value),

            'object' === $propertyType => $this->propertyObject($object, $class, $property, $type, $value),

            DateTime::class === $propertyType, DateTimeImmutable::class === $propertyType => $this->propertyDateTime(
                $object,
                $class,
                $property,
                $type,
                $value
            ),

            DateInterval::class === $propertyType => $this->propertyDateInterval(
                $object,
                $class,
                $property,
                $type,
                $value
            ),

            is_subclass_of(
                $propertyType,
                BackedEnum::class
            ) => $this->propertyBackedEnum($object, $class, $property, $type, $value),

            class_exists($propertyType) => $this->propertyFromInstance($object, $class, $property, $type, $value),

            default => throw new Exception\UnsupportedPropertyTypeException(sprintf(
                'The %s.%s property contains an unsupported type %s.',
                $class->getShortName(),
                $property->getName(),
                $type->getName()
            ))
        };
    }

    /**
     * Hydrates the given property with null.
     *
     * @param object              $object
     * @param ReflectionClass     $class
     * @param ReflectionProperty  $property
     * @param ReflectionNamedType $type
     *
     * @throws Exception\InvalidValueException
     *                                         If the given value isn't valid.
     *
     * @return void
     */
    private function propertyNull(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type
    ): void {
        if (!$type->allowsNull()) {
            throw new Exception\InvalidValueException($property, sprintf(
                'The %s.%s property cannot accept null.',
                $class->getShortName(),
                $property->getName()
            ));
        }

        $property->setValue($object, null);
    }

    /**
     * Hydrates the given property with the given boolean value.
     *
     * @param object              $object
     * @param ReflectionClass     $class
     * @param ReflectionProperty  $property
     * @param ReflectionNamedType $type
     * @param mixed               $value
     *
     * @throws Exception\InvalidValueException
     *                                         If the given value isn't valid.
     *
     * @return void
     */
    private function propertyBool(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        mixed $value
    ): void {
        if (!is_bool($value)) {
            // if the value isn't boolean, then we will use filter_var, because it will give us the ability to specify
            // boolean values as strings. this behavior is great for html forms. details at:
            // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L273
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if (!isset($value)) {
                throw new Exception\InvalidValueException($property, sprintf(
                    'The %s.%s property expects a boolean.',
                    $class->getShortName(),
                    $property->getName()
                ));
            }
        }

        $property->setValue($object, $value);
    }

    /**
     * Hydrates the given property with the given integer number.
     *
     * @param object              $object
     * @param ReflectionClass     $class
     * @param ReflectionProperty  $property
     * @param ReflectionNamedType $type
     * @param mixed               $value
     *
     * @throws Exception\InvalidValueException
     *                                         If the given value isn't valid.
     *
     * @return void
     */
    private function propertyInt(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        mixed $value
    ): void {
        if (!is_int($value)) {
            // it's senseless to convert the value type if it's not a number, so we will use filter_var to correct
            // converting the value type to int. also remember that string numbers must be between PHP_INT_MIN and
            // PHP_INT_MAX, otherwise the result will be null. this behavior is great for html forms. details at:
            // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L197
            // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L94
            $value = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

            if (!isset($value)) {
                throw new Exception\InvalidValueException($property, sprintf(
                    'The %s.%s property expects an integer.',
                    $class->getShortName(),
                    $property->getName()
                ));
            }
        }

        $property->setValue($object, $value);
    }

    /**
     * Hydrates the given property with the given number.
     *
     * @param object              $object
     * @param ReflectionClass     $class
     * @param ReflectionProperty  $property
     * @param ReflectionNamedType $type
     * @param mixed               $value
     *
     * @throws Exception\InvalidValueException
     *                                         If the given value isn't valid.
     *
     * @return void
     */
    private function propertyFloat(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        mixed $value
    ): void {
        if (!is_float($value)) {
            // it's senseless to convert the value type if it's not a number, so we will use filter_var to correct
            // converting the value type to float. this behavior is great for html forms. details at:
            // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L342
            $value = filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);

            if (!isset($value)) {
                throw new Exception\InvalidValueException($property, sprintf(
                    'The %s.%s property expects a number.',
                    $class->getShortName(),
                    $property->getName()
                ));
            }
        }

        $property->setValue($object, $value);
    }

    /**
     * Hydrates the given property with the given string.
     *
     * @param object              $object
     * @param ReflectionClass     $class
     * @param ReflectionProperty  $property
     * @param ReflectionNamedType $type
     * @param mixed               $value
     *
     * @throws Exception\InvalidValueException
     *                                         If the given value isn't valid.
     *
     * @return void
     */
    private function propertyString(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        mixed $value
    ): void {
        if (!is_string($value)) {
            throw new Exception\InvalidValueException($property, sprintf(
                'The %s.%s property expects a string.',
                $class->getShortName(),
                $property->getName()
            ));
        }

        $property->setValue($object, $value);
    }

    /**
     * Hydrates the given property with the given array.
     *
     * @param object              $object
     * @param ReflectionClass     $class
     * @param ReflectionProperty  $property
     * @param ReflectionNamedType $type
     * @param mixed               $value
     *
     * @throws Exception\InvalidValueException
     *                                         If the given value isn't valid.
     *
     * @return void
     */
    private function propertyArray(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        mixed $value
    ): void {
        if (is_object($value)) {
            $value = get_object_vars($value);
        }

        if (!is_array($value)) {
            throw new Exception\InvalidValueException($property, sprintf(
                'The %s.%s property expects an array.',
                $class->getShortName(),
                $property->getName()
            ));
        }

        $arrayType = $this->getAttributeInstance($property, ArrayType::class);
        if ($arrayType !== null) {
            $value = $this->hydrateObjectsInArray($value, $arrayType->class, $arrayType->depth);
        }

        $property->setValue($object, $value);
    }

    /**
     * @param array  $array
     * @param string $class
     * @param int    $depth
     *
     * @throws \ReflectionException
     *
     * @return array
     */
    private function hydrateObjectsInArray(array $array, string $class, int $depth): array
    {
        if ($depth > 1) {
            return array_map(function ($child) use ($class, $depth) {
                return $this->hydrateObjectsInArray($child, $class, --$depth);
            }, $array);
        }

        return array_map(function ($object) use ($class) {
            if (is_subclass_of($class, BackedEnum::class)) {
                return $class::tryFrom($object) ?? $object;
            }

            return $this->hydrate($class, $object);
        }, $array);
    }

    /**
     * Hydrates the given property with the given object.
     *
     * @param object              $object
     * @param ReflectionClass     $class
     * @param ReflectionProperty  $property
     * @param ReflectionNamedType $type
     * @param mixed               $value
     *
     * @throws Exception\InvalidValueException
     *                                         If the given value isn't valid.
     *
     * @return void
     */
    private function propertyObject(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        mixed $value
    ): void {
        if (!is_object($value)) {
            throw new Exception\InvalidValueException($property, sprintf(
                'The %s.%s property expects an object.',
                $class->getShortName(),
                $property->getName()
            ));
        }

        $property->setValue($object, $value);
    }

    /**
     * Hydrates the given property with the given date-time.
     *
     * @param object              $object
     * @param ReflectionClass     $class
     * @param ReflectionProperty  $property
     * @param ReflectionNamedType $type
     * @param mixed               $value
     *
     * @throws Exception\InvalidValueException
     *                                         If the given value isn't valid.
     *
     * @return void
     */
    private function propertyDateTime(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        mixed $value
    ): void {
        /** @var class-string<DateTime|DateTimeImmutable> */
        $className = $type->getName();

        $property->setValue($object, match (true) {
            is_int($value) => (new $className())->setTimestamp($value),

            is_string($value) && ctype_digit($value) => (new $className())->setTimestamp((int) $value),

            is_string($value) && false !== strtotime($value) => new $className($value),

            default => throw new Exception\InvalidValueException($property, sprintf(
                'The %s.%s property expects a valid date-time string or timestamp.',
                $class->getShortName(),
                $property->getName()
            ))
        });
    }

    /**
     * Hydrates the given property with the given date-interval.
     *
     * @param object              $object
     * @param ReflectionClass     $class
     * @param ReflectionProperty  $property
     * @param ReflectionNamedType $type
     * @param mixed               $value
     *
     * @throws Exception\InvalidValueException
     *                                         If the given value isn't valid.
     *
     * @return void
     */
    private function propertyDateInterval(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        mixed $value
    ): void {
        if (!is_string($value)) {
            throw new Exception\InvalidValueException($property, sprintf(
                'The %s.%s property expects a string.',
                $class->getShortName(),
                $property->getName()
            ));
        }

        /** @var class-string<DateInterval> */
        $className = $type->getName();

        try {
            $dateInterval = new $className($value);
        } catch (\Exception $e) {
            throw new Exception\InvalidValueException($property, sprintf(
                'The %s.%s property expects a valid date-interval string based on ISO 8601.',
                $class->getShortName(),
                $property->getName()
            ));
        }

        $property->setValue($object, $dateInterval);
    }

    /**
     * Hydrates the given property with the given backed-enum.
     *
     * @param object              $object
     * @param ReflectionClass     $class
     * @param ReflectionProperty  $property
     * @param ReflectionNamedType $type
     * @param mixed               $value
     *
     * @throws Exception\InvalidValueException
     *                                         If the given value isn't valid.
     *
     * @return void
     */
    private function propertyBackedEnum(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        mixed $value
    ): void {
        /** @var class-string<BackedEnum> */
        $enumName = $type->getName();
        $enumReflection = new ReflectionEnum($enumName);

        /** @var ReflectionNamedType */
        $enumType = $enumReflection->getBackingType();
        $enumTypeName = $enumType->getName();

        // support for HTML forms...
        if ('int' === $enumTypeName && is_string($value)) {
            $value = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        }

        if (('int' === $enumTypeName && !is_int($value)) ||
            ('string' === $enumTypeName && !is_string($value))) {
            throw new Exception\InvalidValueException($property, sprintf(
                'The %s.%s property expects the following type: %s.',
                $class->getShortName(),
                $property->getName(),
                $enumTypeName
            ));
        }

        $enum = $enumName::tryFrom($value);
        if (!isset($enum)) {
            $allowedCases = [];
            foreach ($enumName::cases() as $case) {
                $allowedCases[] = $case->value;
            }

            throw new Exception\InvalidValueException($property, sprintf(
                'The %s.%s property expects one of the following values: %s.',
                $class->getShortName(),
                $property->getName(),
                implode(', ', $allowedCases)
            ));
        }

        $property->setValue($object, $enum);
    }

    /**
     * Hydrates the given property with the given one association.
     *
     * @param object              $object
     * @param ReflectionClass     $class
     * @param ReflectionProperty  $property
     * @param ReflectionNamedType $type
     * @param mixed               $value
     *
     * @throws Exception\InvalidValueException
     *                                         If the given value isn't valid.
     *
     * @return void
     */
    private function propertyFromInstance(
        object $object,
        ReflectionClass $class,
        ReflectionProperty $property,
        ReflectionNamedType $type,
        mixed $value
    ): void {
        if (!is_array($value) && !is_object($value)) {
            throw new Exception\InvalidValueException($property, sprintf(
                'The %s.%s property expects an associative array or object.',
                $class->getShortName(),
                $property->getName()
            ));
        }

        $property->setValue($object, $this->hydrate($type->getName(), $value));
    }
}
