<?php

namespace Shore\Framework\Http\Request;

use Shore\Framework\Io\UploadedFile;

class UploadedFiles
{
    /**
     * Holds all uploaded files
     *
     * @var \Shore\Framework\Io\UploadedFile[]
     */
    protected $files = [];

    /**
     * UploadedFiles constructor.
     *
     * @param array $files List of uploaded files
     *
     * @throws \Exception
     */
    public function __construct(array $files)
    {
        if (count($files) > 0 && is_array($files[0]['name'])) {
            $files = $this->marshalMultiNameFiles($files);
        }

        foreach ($files as $name => $data) {
            $this->addFile(new UploadedFile($data));
        }
    }

    /**
     * Adds a file to the uploaded files collection
     *
     * @param \Shore\Framework\Io\UploadedFile $file File to add
     * @param string|null                      $name Name of the field the file has been uploaded as. Defaults to the
     *                                               filename.
     */
    public function addFile(?UploadedFile $file, ?string $name = null): void
    {
        $this->files[$name ?? $file->getFilename()] = $file;
    }

    /**
     * Retrieves a single file by field name from the uploaded files collection
     *
     * @param string $name
     *
     * @return \Shore\Framework\Io\UploadedFile
     */
    public function getFile(string $name): UploadedFile
    {
        return $this->files[$name];
    }

    /**
     * Handles multiple uploaded files in a sub-key of the input data, eg. "photos[photo_1]". That will result in
     * a... special... layout of the files array:
     * Array (
     *   [photos] => Array (
     *      [name] => Array (
     *          [photo_1] => MyFile.txt
     *          [photo_2] => MyFile.jpg
     *      ),
     *      [type] => Array (
     *          [photo_1] => text/plain
     *          [photo_2] => image/jpeg
     *      ),
     *      [tmp_name] => Array (
     *          [photo_1] => /tmp/php/php1h4j1o
     *          [photo_2] => /tmp/php/php6hst32
     *      ),
     * // ...
     *
     * @param array $unmarshaledFiles
     *
     * @return array
     */
    protected function marshalMultiNameFiles(array $unmarshaledFiles): array
    {
        $marshaledFiles = [];

        // Iterate list of top-level field names (eg. "photos", "files")
        foreach ($unmarshaledFiles as $fieldName => $files) {
            // Iterate PHP uploaded file info keys (eg. "tmp_name", "type")
            foreach ($files as $field => $fieldFiles) {
                // Iterate sub-key field names (eg. "photos[photo_1]", "photos[photo_2]")
                foreach ($fieldFiles as $fileName => $value) {
                    // Restore the original field name for later access
                    $currentName = "${fieldName}[$fileName]";

                    if (! $marshaledFiles[$currentName]) {
                        $marshaledFiles[$currentName] = [];
                    }

                    $marshaledFiles[$currentName][$field] = $value;
                }
            }
        }

        return $marshaledFiles;
    }
}
