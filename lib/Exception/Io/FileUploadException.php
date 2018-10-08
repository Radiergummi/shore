<?php

namespace Shore\Framework\Exception\Io;

use RuntimeException;

class FileUploadException extends RuntimeException
{
    public function __construct(int $reason)
    {
        switch ($reason) {
            case UPLOAD_ERR_NO_FILE:
                $message = 'No file sent';
                break;

            case UPLOAD_ERR_PARTIAL:
                $message = 'Upload cancelled during transmission';
                break;

            case UPLOAD_ERR_CANT_WRITE:
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = 'Cannot store temporary upload';
                break;

            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $message = 'Exceeded upload file size limit';
                break;

            default:
                $message = 'Unknown upload error';
        }

        parent::__construct($message);
    }
}
