<?php

namespace SergiX44\Hydrator\Annotation;

use Attribute;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class SkipConstructor
{
}
