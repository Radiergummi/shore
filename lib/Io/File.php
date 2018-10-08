<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 08.10.2018
 * Time: 08:45
 */

namespace Shore\Framework\Io;

use Exception;

/**
 * File
 * ====
 * Represents a file entity
 *
 * @package Shore\Framework\Io
 */
class File extends FilesystemItem
{
    /**
     * Holds the file data handle. This will only be populated if any of the non-metadata methods is invoked.
     *
     * @var \SplFileObject
     */
    protected $handle;

    /**
     * Holds the current handle open mode.
     *
     * @var string
     */
    protected $currentMode;

    /**
     * Guesses the MIME type of a file
     *
     * @return string
     */
    public function guessMimeType(): string
    {
        $mimeType = mime_content_type($this->getPath());

        if ($mimeType === false) {
            return '';
        }

        return $mimeType;
    }

    /**
     * Alias for read()
     *
     * @return string
     * @throws \Exception
     */
    public function getContent(): string
    {
        return $this->read();
    }

    /**
     * Reads the file. This will set the open mode to read-only.
     *
     * @return string
     * @throws \Exception
     */
    public function read(): string
    {
        if (! $this->isReadable()) {
            throw new Exception('File is not readable');
        }

        $size = $this->getSize();

        return $this
            ->getHandle(static::OPEN_MODE_READ)
            ->fread($size);
    }

    /**
     * Overwrites the file with the supplied data string.
     *
     * @param string $data
     *
     * @throws \Exception
     */
    public function write(string $data): void
    {
        if (! $this->isWritable()) {
            throw new Exception('File is not writable');
        }

        $this
            ->getHandle(static::OPEN_MODE_WRITE)
            ->fwrite($data);
    }

    /**
     * Appends to the file content.
     *
     * @param string $data
     *
     * @throws \Exception
     */
    public function append(string $data): void
    {
        if (! $this->isWritable()) {
            throw new Exception('File is not writable');
        }

        $this
            ->getHandle(static::OPEN_MODE_APPEND)
            ->fwrite($data);
    }

    /**
     * Renames the file
     *
     * @param string $newName New name of the item, without directory
     *
     * @return \Shore\Framework\Io\FilesystemItem Item instance
     */
    public function rename(string $newName): FilesystemItem
    {
        $newPath = $this->getParentDirectory() . DIRECTORY_SEPARATOR . $newName;

        rename($this->getPath(), $newPath);

        $this->path = $newPath;

        $this->dropMeta();

        return $this;
    }

    /**
     * Moves the file to a new path
     *
     * @param string      $destinationPath New filesystem path
     * @param string|null $destinationName Optional new name. If omitted, the current name will be used.
     *
     * @return \Shore\Framework\Io\FilesystemItem
     * @throws \Exception
     */
    public function move(string $destinationPath, string $destinationName = null): FilesystemItem
    {
        $path = static::normalizePath($destinationPath);

        $path = ! is_null($destinationName)
            ? $path . DIRECTORY_SEPARATOR . $destinationName
            : $path . DIRECTORY_SEPARATOR . $this->getFilename();

        rename($this->getPath(), $path);

        $this->path = $path;

        $this->dropMeta();

        return $this;
    }

    /**
     * Copies the file to a new path
     *
     * @param string      $destinationPath Destination filesystem path
     * @param string|null $destinationName Optional new name. If omitted, the current name will be used.
     *
     * @return \Shore\Framework\Io\FilesystemItem Copied instance
     * @throws \Exception
     */
    public function copy(string $destinationPath, string $destinationName = null): FilesystemItem
    {
        $path = static::normalizePath($destinationPath);

        $path = ! is_null($destinationName)
            ? $path . DIRECTORY_SEPARATOR . $destinationName
            : $path . DIRECTORY_SEPARATOR . $this->getFilename();

        copy($this->getPath(), $path);

        return new File($path);
    }

    /**
     * Deletes the file from the filesystem.
     *
     * @returns void
     */
    public function delete(): void
    {
        unlink($this->getPath());
    }
}
