<?php

namespace Shore\Framework\Io;

use DirectoryIterator;
use Iterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Shore\Framework\Specifications\DirectoryInterface;
use Shore\Framework\Specifications\FileInterface;
use Shore\Framework\Specifications\FilesystemItemInterface;

/**
 * Directory
 * =========
 * Represents a directory on the file system. Implements an iterator that returns file or directory instances.
 *
 * @package Shore\Framework\Io
 */
class Directory extends FilesystemItem implements Iterator, DirectoryInterface
{
    /**
     * Holds a lazy-loaded iterator for the directory
     *
     * @var DirectoryIterator
     */
    protected $iterator;

    /**
     * Retrieves all children of the directory. Shorthand for iterating by yourself.
     *
     * @return array
     */
    public function getChildren(): array
    {
        $children = [];

        foreach ($this as $child) {
            $children[] = $child;
        }

        return $children;
    }

    /**
     * Retrieves the number of children items
     *
     * @return int
     */
    public function getChildrenCount(): int
    {
        return iterator_count($this->getIterator());
    }

    /**
     * Creates a file in the directory
     *
     * @param string $fileName
     * @param string $content
     *
     * @return \Shore\Framework\Specifications\FileInterface
     * @throws \Exception
     */
    public function createFile(string $fileName, ?string $content = null): FileInterface
    {
        $path = $this->getPath() . DIRECTORY_SEPARATOR . $fileName;

        $file = new File($path);

        if ($content) {
            $file->write($content);
        }

        return $file;
    }

    /**
     * Creates a directory in the directory
     *
     * @param string $name
     *
     * @return \Shore\Framework\Specifications\DirectoryInterface
     * @throws \Exception
     */
    public function createDirectory(string $name): DirectoryInterface
    {
        $path = $this->getPath() . DIRECTORY_SEPARATOR . $name;

        // Creates the directory recursively (mkdir -p)
        mkdir($path, 0755, true);

        return new Directory($path);
    }

    /**
     * Retrieves a file from the directory
     *
     * @param string $fileName
     *
     * @return \Shore\Framework\Specifications\FileInterface
     * @throws \Exception
     */
    public function getFile(string $fileName): FileInterface
    {
        $path = $this->getPath() . DIRECTORY_SEPARATOR . $fileName;

        return new File($path);
    }

    /**
     * Retrieves an iterator for the current directory.
     *
     * @return \DirectoryIterator
     */
    public function getIterator(): DirectoryIterator
    {
        if (! $this->iterator) {
            $this->iterator = new DirectoryIterator($this->getPath());
        }

        return $this->iterator;
    }

    /**
     * Retrieves a recursive iterator for the current directory.
     *
     * @return \RecursiveIteratorIterator
     */
    public function getRecursiveIterator(): RecursiveIteratorIterator
    {
        $iterator = new RecursiveDirectoryIterator(
            $this->getPath(),
            RecursiveDirectoryIterator::SKIP_DOTS
        );

        return new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
    }

    /**
     * Return the current element
     *
     * @return null|\Shore\Framework\Io\Directory|\Shore\Framework\Io\File
     * @throws \Exception
     * @link              https://php.net/manual/en/iterator.current.php
     */
    public function current()
    {
        /** @var \SplFileInfo $current */
        $current = $this->getIterator()->current();

        if ($current) {
            $path = $current->getRealPath();

            return $current->isFile()
                ? new File($path)
                : new Directory($path);
        }

        return null;
    }

    /**
     * Move forward to next element
     *
     * @return void Any returned value is ignored.
     * @link        https://php.net/manual/en/iterator.next.php
     */
    public function next(): void
    {
        $this->getIterator()->next();
    }

    /**
     * Return the key of the current element
     *
     * @return mixed scalar on success, or null on failure.
     * @link         https://php.net/manual/en/iterator.key.php
     */
    public function key()
    {
        return $this->getIterator()->key();
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean The return value will be casted to boolean and then evaluated. Returns true on success or false
     *                 on failure.
     * @link           https://php.net/manual/en/iterator.valid.php
     */
    public function valid(): bool
    {
        return $this->getIterator()->valid();
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @return void Any returned value is ignored.
     * @link        https://php.net/manual/en/iterator.rewind.php
     */
    public function rewind(): void
    {
        $this->getIterator()->rewind();
    }

    /**
     * Renames the directory
     *
     * @param string $newName New name of the directory, without parent directory
     *
     * @return \Shore\Framework\Specifications\FilesystemItemInterface Item instance
     */
    public function rename(string $newName): FilesystemItemInterface
    {
        $newPath = $this->getParentDirectory() . PHP_EOL . $newName;

        rename($this->getPath(), $newPath);

        $this->path = $newPath;

        $this->dropMeta();

        return $this;
    }

    /**
     * Moves a directory on the filesystem
     *
     * @param string      $destinationPath
     * @param string|null $destinationName
     *
     * @return \Shore\Framework\Specifications\FilesystemItemInterface
     * @throws \Exception
     */
    public function move(string $destinationPath, ?string $destinationName = null): FilesystemItemInterface
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
     * Copies the directory recursively to a new destination
     *
     * @param string      $destinationPath
     * @param string|null $destinationName
     *
     * @return \Shore\Framework\Specifications\FilesystemItemInterface
     * @throws \Exception
     */
    public function copy(string $destinationPath, ?string $destinationName = null): FilesystemItemInterface
    {
        $path = static::normalizePath($destinationPath);

        $path = ! is_null($destinationName)
            ? $path . DIRECTORY_SEPARATOR . $destinationName
            : $path . DIRECTORY_SEPARATOR . $this->getFilename();

        // Create the target directory
        mkdir($path);

        // Iterate the source directory recursively
        /** @var \SplFileInfo $child */
        foreach ($iterator = $this->getRecursiveIterator() as $child) {
            // Create directories in the destination path
            if ($child->isDir()) {
                // RecursiveIteratorIterator forwards the getSubPathName() call to the RecursiveDirectoryIterator,
                // but PhpStorm can't know that
                /** @noinspection PhpUndefinedMethodInspection */
                mkdir($path . DIRECTORY_SEPARATOR . $iterator->getSubPathName());

                continue;
            }

            // Copy files into the destination path
            /** @noinspection PhpUndefinedMethodInspection */
            copy($child->getRealPath(), $path . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
        }
    }

    /**
     * Deletes the directory recursively
     *
     * @return void
     */
    public function delete(): void
    {
        /** @var \SplFileInfo $child */
        foreach ($this->getRecursiveIterator() as $child) {
            if ($child->isDir()) {
                rmdir($child->getRealPath());
            } else {
                unlink($child->getRealPath());
            }
        }

        rmdir($this->getPath());
    }
}
