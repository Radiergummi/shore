<?php

namespace Shore\Framework\Specifications;

use SplFileObject;

interface FileInterface
{
    /**
     * Generates an SplFileObject handle for the file, if not already generated.
     *
     * @param string $openMode Mode to open the file in. Can use the OPEN_MODE_ constants for the file.
     *
     * @return \SplFileObject
     */
    public function getHandle(?string $openMode = FilesystemItemInterface::OPEN_MODE_READ_WRITE): SplFileObject;

    /**
     * Guesses the MIME type of a file
     *
     * @return string
     */
    public function guessMimeType(): string;

    /**
     * Retrieves the extension of a file on the filesystem
     *
     * @return string
     */
    public function getExtension(): string;

    /**
     * Alias for read()
     *
     * @return string
     * @throws \Exception
     */
    public function getContent(): string;

    /**
     * Reads the file. This will set the open mode to read-only.
     *
     * @return string
     * @throws \Exception
     */
    public function read(): string;

    /**
     * Overwrites the file with the supplied data string.
     *
     * @param string $data
     *
     * @throws \Exception
     */
    public function write(string $data): void;

    /**
     * Appends to the file content.
     *
     * @param string $data
     *
     * @throws \Exception
     */
    public function append(string $data): void;
}
