<?php

namespace Shore\Framework\Specifications;

use DirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

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


    /**
     * Normalizes a path. Useful for virtual (non-existing) files, as it doesn't rely on realpath(1)
     *
     * @param string $path Path to normalize
     *
     * @return string
     * @throws \Exception
     */
    public static function normalizePath(string $path): string;

    /**
     * Generates an SplFileInfo object for the item, if not already generated.
     *
     * @return \SplFileInfo
     */
    public function getMeta(): SplFileInfo;

    /**
     * Retrieves the size of the item
     *
     * @return int
     */
    public function getSize(): int;

    /**
     * Checks whether the item is a directory
     *
     * @return bool
     */
    public function isDirectory(): bool;

    /**
     * Checks whether the item is a file
     *
     * @return bool
     */
    public function isFile(): bool;

    /**
     * Checks whether the item is a symbolic link
     *
     * @return bool
     */
    public function isLink(): bool;

    /**
     * Checks whether the item is readable
     *
     * @return bool
     */
    public function isReadable(): bool;

    /**
     * Checks whether the item is writable
     *
     * @return bool
     */
    public function isWritable(): bool;

    /**
     * Retrieves the numeric owning user ID
     *
     * @return int
     */
    public function getOwner(): int;

    /**
     * Retrieves the numeric owning group ID
     *
     * @return int
     */
    public function getGroup(): int;

    /**
     * Retrieves the permissions of an item, as an integer.
     *
     * @return int
     */
    public function getPermissions(): int;

    /**
     * Retrieves a timestamp of the item, where the type can be any of access, modification or change time (A, M or C).
     *
     * @param int $timestampType
     *
     * @return int
     */
    public function getTimestamp(int $timestampType = FilesystemItemInterface::TIMESTAMP_MODIFICATION): int;

    /**
     * Retrieves the full, normalized path to the item
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Retrieves the basename of an item. Optionally, a suffix to remove from the name can be passed.
     *
     * @param string|null $suffix Optional suffix to remove, if it matches
     *
     * @return string
     */
    public function getBasename(string $suffix = null): string;

    /**
     * Retrieves the filename of an item, without leading path, but including extension, if any.
     *
     * @return string
     */
    public function getFilename(): string;

    /**
     * Retrieves the parent directory of an item on the file system
     *
     * @return string
     */
    public function getParentDirectory(): string;

    /**
     * Renames the filesystem item
     *
     * @param string $newName New name of the item, without directory
     *
     * @return \Shore\Framework\Specifications\FilesystemItemInterface Item instance
     */
    public function rename(string $newName): FilesystemItemInterface;

    /**
     * Moves the filesystem item to a new path
     *
     * @param string      $destinationPath New filesystem path
     * @param string|null $destinationName Optional new name. If omitted, the current name will be used.
     *
     * @return \Shore\Framework\Specifications\FilesystemItemInterface
     */
    public function move(string $destinationPath, string $destinationName = null): FilesystemItemInterface;

    /**
     * Copies the filesystem item to a new path
     *
     * @param string      $destinationPath Destination filesystem path
     * @param string|null $destinationName Optional new name. If omitted, the current name will be used.
     *
     * @return \Shore\Framework\Specifications\FilesystemItemInterface
     */
    public function copy(string $destinationPath, string $destinationName = null): FilesystemItemInterface;

    /**
     * Deletes the item from the filesystem.
     *
     * @returns void
     */
    public function delete(): void;
}
