<?php

namespace Shore\Framework\Http\Request;

use Exception;
use Shore\Framework\DataProvider;

class Body extends DataProvider
{
    /**
     * Body constructor.
     *
     * @param array $body
     *
     * @throws \Exception
     */
    public function __construct(array $body = [])
    {
        if (empty($body)) {
            $body = $this->parseRawBody();
        }

        parent::__construct($body);
    }

    /**
     * Parses the raw request body. This assumes the body has not been sent as an URL-encoded representation but JSON
     * instead, so we try to parse the body as JSON and set the body data from the result.
     * Since modern PHP versions are able to throw on parsing errors, we make backwards-compatible use of that
     * capability. If the json_decode call doesn't throw, we'll do so manually.
     *
     * @return array
     * @throws \Exception
     */
    protected function parseRawBody(): array
    {
        $input = file_get_contents('php://input');

        if (strlen($input) === 0) {
            return [];
        }

        try {
            // If the throw flag isn't defined, define a fallback flag now. This only affects PHP versions < 7.3
            // and can be removed once we don't use earlier versions anymore. However, this check is extremely
            // efficient and does not matter much.
            if (! defined('JSON_THROW_ON_ERROR')) {
                define('JSON_THROW_ON_ERROR', 0);
            }

            // Try to JSON-decode the request data
            $data = json_decode(
                $input,
                true,
                512,
                JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING
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
