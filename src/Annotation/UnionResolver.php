<?php


namespace SergiX44\Hydrator\Annotation;

use Attribute;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionType;
use ReflectionUnionType;
use SergiX44\Hydrator\UnionTypeResolver;

/**
 * @Annotation
 *
 * @Target({"PROPERTY"})
 *
 * @NamedArgumentConstructor
 *
 * @Attributes({
 *   @Attribute("class", type="class-string", required=true),
 * })
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class UnionResolver
{

    /**
     * The attribute value
     *
     * @var class-string
     */
    public string $class;

    /**
     * Constructor of the class
     *
     * @param class-string $class
     */
    public function __construct(string $class)
    {
        if (!is_subclass_of($class, UnionTypeResolver::class)) {
            throw new InvalidArgumentException(sprintf(
                'The %s class must implement the %s interface.',
                $class,
                UnionTypeResolver::class
            ));
        }

        $this->class = $class;
    }

    /**
     * @param ReflectionUnionType $type
     * @return ReflectionType
     * @throws \ReflectionException
     */
    public function resolve(ReflectionUnionType $type, array $data): ReflectionType
    {
        $class = new ReflectionClass($this->class);

        /** @var UnionTypeResolver $instance */
        if ($class->getConstructor()?->getNumberOfRequiredParameters() > 0) {
            $instance = $class->newInstanceWithoutConstructor();
        } else {
            $instance = $class->newInstance();
        }

        return $instance->resolve($type, $data);
    }
}
