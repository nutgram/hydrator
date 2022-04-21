<?php



namespace SergiX44\Hydrator;

/**
 * Import classes
 */

/**
 * ObjectCollectionInterface
 */
interface ObjectCollectionInterface
{

    /**
     * Gets the collection type
     *
     * @return class-string
     */
    public function getItemClassName() : string;

    /**
     * Checks by the given key if an object exists in the collection
     *
     * @param array-key $key
     *
     * @return bool
     */
    public function has($key) : bool;

    /**
     * Adds the given object to the collection with the given key
     *
     * @param array-key $key
     * @param object $object
     *
     * @return void
     */
    public function add($key, object $object) : void;

    /**
     * Gets an object from the collection by the given key
     *
     * @param array-key $key
     *
     * @return object|null
     */
    public function get($key) : ?object;

    /**
     * Gets all objects from the collection
     *
     * @return array<array-key, object>
     */
    public function all() : array;

    /**
     * Checks if the collection is empty
     *
     * @return bool
     */
    public function isEmpty() : bool;
}
