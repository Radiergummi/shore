<?php

namespace Shore\Framework\Io;

use Exception;
use Shore\Framework\Exception\Io\FileUploadException;
use Shore\Framework\Facades\Hash;

class UploadedFile extends File
{
    public function __construct(array $fileInfo)
    {
        if (! isset($fileInfo['tmp_name']) || ! isset($fileInfo['error'])) {
            throw new Exception('Invalid uploaded file info, a key is missing');
        }

        if ($fileInfo['error'] !== UPLOAD_ERR_OK) {
            throw new FileUploadException($fileInfo['error']);
        }

        parent::__construct($fileInfo['tmp_name']);
    }

    /**
     * Saves the uploaded file at the target location
     *
     * @param string      $path
     * @param null|string $newName
     *
     * @return \Shore\Framework\Io\File
     * @throws \Exception
     */
    public function save(string $path, ?string $newName = null): File
    {
        $name = is_null($newName) ? Hash::from($this) : $newName;

        $this->move(static::normalizePath($path) . DIRECTORY_SEPARATOR . $name);
    }
}
