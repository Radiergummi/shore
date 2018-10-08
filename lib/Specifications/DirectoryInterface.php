<?php

namespace Shore\Framework\Specifications;

use DirectoryIterator;
use RecursiveIteratorIterator;

interface DirectoryInterface
{

    /**
     * Retrieves all children of the directory. Shorthand for iterating by yourself.
     *
     * @return array
     */
    public function getChildren(): array;

    /**
     * Retrieves the number of children items
     *
     * @return int
     */
    public function getChildrenCount(): int;

    /**
     * Creates a file in the directory
     *
     * @param string $fileName
     * @param string $content
     *
     * @return \Shore\Framework\Specifications\FileInterface
     */
    public function createFile(string $fileName, ?string $content = null): FileInterface;

    /**
     * Retrieves a file from the directory
     *
     * @param string $fileName
     *
     * @return \Shore\Framework\Specifications\FileInterface
     */
    public function getFile(string $fileName): FileInterface;

    /**
     * Retrieves an iterator for the current directory.
     *
     * @return \DirectoryIterator
     */
    public function getIterator(): DirectoryIterator;

    /**
     * Retrieves a recursive iterator for the current directory.
     *
     * @return \RecursiveIteratorIterator
     */
    public function getRecursiveIterator(): RecursiveIteratorIterator;
}
