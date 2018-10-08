<?php

namespace Shore\Framework;

/**
 * Hashable interface
 * ==================
 * Denotes an object as able to automatically get hashed, if requested via the Hash class. This is useful to retrieve
 * file hashes, for example.
 *
 * @package Shore\Framework
 */
interface Hashable
{
    /**
     * Allows to automatically hash the object from the Hash class, if it exists. The method will receive the requested
     * algorithm as its first parameter.
     *
     * @param string $algorithm
     *
     * @return string
     */
    public function toHash(string $algorithm): string;
}
