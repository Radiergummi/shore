<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 08.10.2018
 * Time: 08:45
 */

namespace Shore\Framework\Io;

use Exception;
use InvalidArgumentException;
use Shore\Framework\Exception\Io\InvalidPathException;
use SplFileInfo;
use SplFileObject;

abstract class FilesystemItem
{
    public const OPEN_MODE_APPEND = 'a';

    public const OPEN_MODE_APPEND_READ = 'a+';

    public const OPEN_MODE_READ = 'r';

    public const OPEN_MODE_READ_WRITE = 'r+';

    public const OPEN_MODE_READ_WRITE_NEW = 'w+';

    public const OPEN_MODE_WRITE = 'w';

    public const TIMESTAMP_ACCESS = 0;

    public const TIMESTAMP_CHANGE = 1;

    public const TIMESTAMP_MODIFICATION = 2;

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
    public function __construct(string $path, bool $verifyPath = true)
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

    /**
     * Generates an SplFileObject handle for the file, if not already generated.
     *
     * @param string $openMode Mode to open the file in. Can use the OPEN_MODE_ constants for the file.
     *
     * @return \SplFileObject
     */
    public function getHandle(string $openMode = self::OPEN_MODE_READ_WRITE): SplFileObject
    {
        if (! $this->handle || $this->currentMode !== $openMode) {
            $this->currentMode = $openMode;
            $this->handle = $this->getMeta()->openFile($openMode);
        }

        return $this->handle;
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

    public function getTimestamp(int $timestampType = self::TIMESTAMP_MODIFICATION): int
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

    public function getBasename(string $suffix = null): string
    {
        return $this->getMeta()->getBasename($suffix);
    }

    public function getFilename(): string
    {
        return $this->getMeta()->getFilename();
    }

    public function getExtension(): string
    {
        return $this->getMeta()->getExtension();
    }

    public function getParentDirectory(): string
    {
        return $this->getMeta()->getPath();
    }

    /**
     * Renames the filesystem item
     *
     * @param string $newName New name of the item, without directory
     *
     * @return \Shore\Framework\Io\FilesystemItem Item instance
     */
    abstract public function rename(string $newName): FilesystemItem;

    /**
     * Moves the filesystem item to a new path
     *
     * @param string      $destinationPath New filesystem path
     * @param string|null $destinationName Optional new name. If omitted, the current name will be used.
     *
     * @return \Shore\Framework\Io\FilesystemItem
     */
    abstract public function move(string $destinationPath, string $destinationName = null): FilesystemItem;

    /**
     * Copies the filesystem item to a new path
     *
     * @param string      $destinationPath Destination filesystem path
     * @param string|null $destinationName Optional new name. If omitted, the current name will be used.
     *
     * @return \Shore\Framework\Io\FilesystemItem
     */
    abstract public function copy(string $destinationPath, string $destinationName = null): FilesystemItem;

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
