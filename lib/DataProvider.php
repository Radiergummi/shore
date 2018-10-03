<?php

namespace Shore\Framework;

use Countable;
use JsonSerializable;

/**
 * Provides a basic abstraction layer for read-only accessor objects
 *
 * @package Shore\Framework
 */
abstract class DataProvider implements Countable, JsonSerializable
{
    /**
     * Holds the data to provide
     *
     * @var array
     */
    protected $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Retrieves a field from the store. Optionally, a fallback value can be passed that will be returned for fields
     * that don't exist.
     *
     * @param string $fieldName Name of the field to retrieve
     * @param mixed  $fallback  Optional fallback value
     *
     * @return mixed
     */
    public function get(string $fieldName, $fallback = null)
    {
        return $this->data[$fieldName] ?? $fallback;
    }

    /**
     * Checks whether a field exists.
     *
     * @param string $fieldName Name of the field to check
     *
     * @return bool
     */
    public function has(string $fieldName): bool
    {
        return isset($this->data[$fieldName]);
    }

    /**
     * Retrieves a field from the body via magic properties. This allows to directly access a body field via name:
     * `$request->body->myField`. Will return null for fields that don't exist.
     *
     *
     * @param string $fieldName Name of the field to retrieve
     *
     * @return mixed
     */
    public function __get(string $fieldName)
    {
        return $this->get($fieldName);
    }

    /**
     * Checks whether a field exists via magic method proxy.
     *
     * @param string $fieldName Name of the field to check
     *
     * @return bool
     */
    public function __isset(string $fieldName): bool
    {
        return $this->has($fieldName);
    }

    /**
     * Count elements of an object
     *
     * @return int Number of fields in the body
     * @link  https://php.net/manual/en/countable.count.php
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return mixed data which can be serialized by *json_encode*,
     * which is a value of any type other than a resource.
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     */
    public function jsonSerialize()
    {
        return $this->data;
    }

    public function toArray()
    {
        return $this->data;
    }
}
