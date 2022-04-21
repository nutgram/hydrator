<?php



namespace SergiX44\Hydrator;

/**
 * HydratorInterface
 */
interface HydratorInterface
{

    /**
     * Hydrates the given object with the given data
     *
     * @param class-string|object $object
     * @param array|object $data
     *
     * @return object
     */
    public function hydrate($object, $data) : object;

    /**
     * Hydrates the given object with the given JSON
     *
     * @param class-string|object $object
     * @param string $json
     * @param ?int $flags
     *
     * @return object
     */
    public function hydrateWithJson($object, string $json, ?int $flags) : object;
}
