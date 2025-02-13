<?php

namespace SergiX44\Hydrator\Resolver;

use Attribute;
use ReflectionClass;
use SergiX44\Hydrator\Annotation\ConcreteResolver;

#[Attribute(Attribute::TARGET_CLASS)]
class AnonymousResolver extends ConcreteResolver
{
    public function concreteFor(array $data, ReflectionClass $class): ?string
    {
        if (!$class->isAbstract()) {
            return $class->getName();
        }

        $className = $class->getName();
        return (eval("return new class extends \\$className {
            private array \$attributes = [];

            public function __set(string \$name, mixed \$value): void
            {
                \$this->attributes[\$name] = \$value;
            }

            public function __get(string \$name): mixed
            {
                return \$this->attributes[\$name] ?? null;
            }
        };"))::class;
    }
}
