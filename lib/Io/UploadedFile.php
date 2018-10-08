<?php

namespace Shore\Framework\Io;

use Exception;
use Shore\Framework\Exception\Io\FileUploadException;

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
}
