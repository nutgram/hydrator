<?php

namespace SergiX44\Hydrator\Annotation;

use Attribute;
use Psr\Container\ContainerInterface;
use ReflectionMethod;
use ReflectionParameter;

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
            static function (ReflectionParameter $parameter) use ($container) {
                if (!$container->has($parameter->getType()?->getName())) {
                    if ($parameter->isDefaultValueAvailable()) {
                        return $parameter->getDefaultValue();
                    }

                    if ($parameter->allowsNull()) {
                        return null;
                    }
                }

                return $container->get($parameter->getType()?->getName());
            },
            $method->getParameters()
        );
    }
}
