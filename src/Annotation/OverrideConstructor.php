<?php

namespace SergiX44\Hydrator\Annotation;

use Attribute;
use Psr\Container\ContainerInterface;
use ReflectionMethod;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class OverrideConstructor
{
    public function __construct(public string $method)
    {
    }


    public function getArguments(mixed $object, ContainerInterface $container): array
    {
        $method = new ReflectionMethod($object, $this->method);

        return array_map(
            static fn($parameter) => $container->get($parameter->getType()?->getName()),
            $method->getParameters()
        );
    }
}
