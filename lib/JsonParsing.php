<?php

namespace Shore\Framework;

use Exception;

/**
 * JSON parsing trait
 * ==================
 * This trait adds two methods to a class, that allow for safeguarded JSON handling. If anything goes wrong, they will
 * throw an exception.
 *
 * @package Shore\Framework
 */
trait JsonParsing
{
    /**
     * Encodes arbitrary data to JSON
     *
     * @param mixed $data    Data to encode to JSON
     *
     * @param int   $options JSON encode flags
     *
     * @return string Encoded JSON string
     * @throws \Exception If the supplied data can't be encoded
     */
    protected function encodeJson($data, $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE): string
    {
        $encoded = json_encode(
            $data,
            $options
        );

        if (! is_string($encoded)) {
            throw new Exception('Could not encode to JSON');
        }

        return $encoded;
    }

    /**
     * Decodes a JSON string to scalar PHP
     *
     * @param string $json    JSON string to decode
     *
     * @param int    $options JSON decode flags
     *
     * @return mixed
     * @throws \Exception
     */
    protected function decodeJson(string $json, int $options = JSON_BIGINT_AS_STRING)
    {
        try {
            // If the throw flag isn't defined, define a fallback flag now. This only affects PHP versions < 7.3
            // and can be removed once we don't use earlier versions anymore. However, this check is extremely
            // efficient and does not matter much.
            if (! defined('JSON_THROW_ON_ERROR')) {
                define('JSON_THROW_ON_ERROR', 0);
            }

            // Try to JSON-decode the request data
            $data = json_decode(
                $json,
                true,
                512,
                JSON_THROW_ON_ERROR | $options
            );

            $jsonError = json_last_error();

            if ($jsonError !== JSON_ERROR_NONE) {
                throw new Exception(json_last_error_msg(), $jsonError);
            }

            return $data;
        } catch (Exception $exception) {
            throw new Exception('Could not parse request body: ' . $exception->getMessage());
        }
    }
}
