<?php

namespace SergiX44\Hydrator\Exception;

use ReflectionProperty;
use Throwable;

use function sprintf;

class InvalidValueException extends HydrationException
{
    /**
     * The problem property.
     *
     * @var ReflectionProperty
     */
    private $property;

    /**
     * Constructor of the class.
     *
     * @param ReflectionProperty $property
     * @param string             $message
     * @param int                $code
     * @param Throwable|null     $previous
     */
    public function __construct(
        ReflectionProperty $property,
        string $message,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $property->setAccessible(false);
        $this->property = $property;
    }

    /**
     * Gets the problem property.
     *
     * @return ReflectionProperty
     */
    final public function getProperty(): ReflectionProperty
    {
        return $this->property;
    }

    /**
     * Gets the problem property path.
     *
     * @return string
     */
    final public function getPropertyPath(): string
    {
        return sprintf('%s.%s', $this->property->getDeclaringClass()->getShortName(), $this->property->getName());
    }
}
