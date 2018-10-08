<?php

namespace Shore\Framework\Io;

use Exception;
use InvalidArgumentException;
use Shore\Framework\Exception\Io\InvalidPathException;
use Shore\Framework\Specifications\FilesystemItemInterface;
use SplFileInfo;

/**
 * Filesystem item
 * ===============
 * Base class for all filesystem items to inherit from. Provides base methods around dealing with metadata properties.
 *
 * @package Shore\Framework\Io
 */
abstract class FilesystemItem implements FilesystemItemInterface
{
    /**
     * Holds the file path
     *
     * @var string
     */
    protected $path;

    /**
     * Holds the file meta data
     *
     * @var SplFileInfo
     */
    protected $meta;

    /**
     * Creates a new file instance
     *
     * @param string $path
     * @param bool   $verifyPath
     *
     * @throws \Exception
     */
    public function __construct(string $path, ?bool $verifyPath = true)
    {
        $exists = file_exists($path);

        if ($verifyPath && ! $exists) {
            throw new InvalidPathException($path);
        }

        $this->path = ! $exists
            ? static::normalizePath($path)
            : realpath($path);
    }

    /**
     * Normalizes a path. Useful for virtual (non-existing) files, as it doesn't rely on realpath(1)
     *
     * @param string $path Path to normalize
     *
     * @return string
     * @throws \Exception
     */
    public static function normalizePath(string $path): string
    {
        $segments = [];

        foreach (explode('/', $path) as $part) {
            // ignore parts that have no value
            if (empty($part) || $part === '.') {
                continue;
            }

            if ($part !== '..') {
                // cool, we found a new part
                array_push($segments, $part);
            } else {
                if (count($segments) > 0) {
                    // going back up? sure
                    array_pop($segments);
                } else {
                    // now, here we don't like
                    throw new Exception('Climbing above the path root is not permitted');
                }
            }
        }

        return implode('/', $segments);
    }

    /**
     * Generates an SplFileInfo object for the file, if not already generated.
     *
     * @return \SplFileInfo
     */
    public function getMeta(): SplFileInfo
    {
        if (! $this->meta) {
            $this->meta = new SplFileInfo($this->path);
        }

        return $this->meta;
    }

    public function getSize(): int
    {
        return $this->getMeta()->getSize();
    }

    public function isDirectory(): bool
    {
        return $this->getMeta()->isDir();
    }

    public function isFile(): bool
    {
        return $this->getMeta()->isFile();
    }

    public function isLink(): bool
    {
        return $this->getMeta()->isLink();
    }

    public function isReadable(): bool
    {
        return $this->getMeta()->isReadable();
    }

    public function isWritable(): bool
    {
        return $this->getMeta()->isWritable();
    }

    public function getOwner(): int
    {
        return $this->getMeta()->getOwner();
    }

    public function getGroup(): int
    {
        return $this->getMeta()->getGroup();
    }

    public function getPermissions(): int
    {
        return $this->getMeta()->getPerms();
    }

    public function getTimestamp(?int $timestampType = self::TIMESTAMP_MODIFICATION): int
    {
        $meta = $this->getMeta();

        switch ($timestampType) {
            case static::TIMESTAMP_ACCESS:
                return $meta->getATime();
                break;

            case static::TIMESTAMP_CHANGE:
                return $meta->getCTime();
                break;

            case static::TIMESTAMP_MODIFICATION:
                return $meta->getMTime();
                break;
        }

        throw new InvalidArgumentException("No such timestamp type: $timestampType");
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getBasename(?string $suffix = null): string
    {
        return $this->getMeta()->getBasename($suffix);
    }

    public function getFilename(): string
    {
        return $this->getMeta()->getFilename();
    }

    /**
     * Retrieves the parent directory of an item on the file system
     *
     * @return string
     */
    public function getParentDirectory(): string
    {
        return $this->getMeta()->getPath();
    }

    /**
     * Renames the filesystem item
     *
     * @param string $newName New name of the item, without directory
     *
     * @return \Shore\Framework\Specifications\FilesystemItemInterface Item instance
     */
    abstract public function rename(string $newName): FilesystemItemInterface;

    /**
     * Moves the filesystem item to a new path
     *
     * @param string      $destinationPath New filesystem path
     * @param string|null $destinationName Optional new name. If omitted, the current name will be used.
     *
     * @return \Shore\Framework\Specifications\FilesystemItemInterface
     */
    abstract public function move(string $destinationPath, ?string $destinationName = null): FilesystemItemInterface;

    /**
     * Copies the filesystem item to a new path
     *
     * @param string      $destinationPath Destination filesystem path
     * @param string|null $destinationName Optional new name. If omitted, the current name will be used.
     *
     * @return \Shore\Framework\Specifications\FilesystemItemInterface
     */
    abstract public function copy(string $destinationPath, ?string $destinationName = null): FilesystemItemInterface;

    /**
     * Deletes the item from the filesystem.
     *
     * @returns void
     */
    abstract public function delete(): void;

    /**
     * Drops the current meta object. This method should be called for modifying operations.
     *
     * @returns void
     */
    protected function dropMeta(): void
    {
        $this->meta = null;
    }
}
