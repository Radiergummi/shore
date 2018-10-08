<?php

namespace Shore\Framework\Http;

use Shore\Framework\Http\Request\Body;
use Shore\Framework\Http\Request\Query;
use Shore\Framework\RequestInterface;

class Request extends Message implements RequestInterface
{
    /**
     * Holds the request input
     *
     * @var array
     */
    protected $server;

    /**
     * Holds the query instance
     *
     * @var \Shore\Framework\Http\Request\Query
     */
    protected $params;

    /**
     * Holds the args
     *
     * @var array
     */
    protected $args = [];

    /**
     * Request constructor.
     *
     * @param array $server
     * @param array $request
     * @param array $query
     * @param array $body
     * @param array $files
     *
     * @throws \Exception If the request body can't be parsed
     */
    public function __construct(
        array $server,
        array $request,
        array $query,
        array $body,
        array $files
    ) {
        $this->server = $server;
        $this->headers = $this->withHeaders(static::marshalHeaders($server));
        $this->body = $this->withBody(new Body($body));
        $this->params = new Query($query);
    }

    /**
     * Creates a new request from PHP global variables
     *
     * @return \Shore\Framework\RequestInterface
     * @throws \Exception
     */
    public static function fromGlobals(): RequestInterface
    {
        return new static($_SERVER, $_REQUEST, $_GET, $_POST, $_FILES);
    }

    protected static function marshalHeaders(array $server)
    {
        $headers = [];

        // Try to use the apache headers function
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers() ?? [];
        }

        foreach ($server as $key => $value) {
            // Apache prefixes environment variables with REDIRECT_
            // if they are added by rewrite rules
            if (strpos($key, 'REDIRECT_') === 0) {
                $key = substr($key, 9);

                // We will not overwrite existing variables with the
                // prefixed versions, though
                if (array_key_exists($key, $server)) {
                    continue;
                }
            }

            if ($value && strpos($key, 'HTTP_') === 0) {
                $name = strtr(strtolower(substr($key, 5)), '_', '-');
                $headers[$name] = $value;

                continue;
            }

            if ($value && strpos($key, 'CONTENT_') === 0) {
                $name = 'content-' . strtolower(substr($key, 8));
                $headers[$name] = $value;

                continue;
            }
        }

        return $headers;
    }

    /**
     * Retrieves the query params
     *
     * @return \Shore\Framework\Http\Request\Query
     */
    public function params(): Query
    {
        return $this->params;
    }

    /**
     * Retrieves the request body
     *
     * @return \Shore\Framework\Http\Request\Body
     */
    public function body(): Body
    {
        return $this->body;
    }

    public function args()
    {
        return $this->args;
    }

    /**
     * Retrieves a body field
     *
     * @param string $fieldName
     * @param mixed  $fallback
     *
     * @return mixed
     */
    public function get(string $fieldName, $fallback = null)
    {
        if ($this->params()->has($fieldName)) {
            return $this->params()->get($fieldName);
        }

        return $this->body()->get(
            $fieldName,
            $fallback
        );
    }

    /**
     * Retrieves the current request URI
     *
     * @param string|null $append Optional string to append to the URI
     *
     * @return string
     */
    public function uri(string $append = null): string
    {
        return $this->server['REQUEST_URI'] . $append;
    }

    /**
     * Sets the request path
     *
     * @param string $path
     *
     * @return \Shore\Framework\RequestInterface
     */
    public function withPath(string $path): RequestInterface
    {
        $this->server['PATH_INFO'] = $path;

        return $this;
    }

    public function path(string $append = null): string
    {
        return $this->server['PATH_INFO'] . $append;
    }

    /**
     * Retrieves the current HTTP request method
     *
     * @return string
     */
    public function method(): string
    {
        return $this->server['REQUEST_METHOD'];
    }

    /**
     * Sets the request method
     *
     * @param string $method
     *
     * @return \Shore\Framework\RequestInterface
     */
    public function withMethod(string $method): RequestInterface
    {
        $this->server['REQUEST_METHOD'] = $method;

        return $this;
    }

    /**
     * Checks whether the request has a body
     *
     * @return bool
     */
    public function hasBody(): bool
    {
        return in_array(
            $this->method(),
            [
                'POST',
                'PUT',
                'PATCH'
            ]
        );
    }

    /**
     * Whether the current request has been submitted from the command line
     *
     * @return bool
     */
    public function cli(): bool
    {
        return PHP_SAPI === 'cli';
    }

    /**
     * Retrieves all request headers
     *
     * @return array
     */
    public function headers(): array
    {
        return $this->loadHeaders();
    }

    /**
     * Retrieves a single header
     *
     * @param string $key name of the header
     * @param mixed  $fallback
     *
     * @return string
     */
    public function header(string $key, $fallback = null): string
    {
        $headers = $this->loadHeaders();

        return $headers[strtolower($key)] ?? $fallback;
    }

    /**
     * Loads all headers via lazy evaluation - headers will only be fetched on the first call.
     * All subsequent calls will use the pre-parsed headers array
     *
     * @return array
     */
    protected function loadHeaders(): array
    {
        if (! isset($this->headers)) {
            $rawHeaders = getallheaders() ?? [];

            foreach ($rawHeaders as $key => $value) {
                $this->headers[strtolower($key)] = $value;
            }
        }

        return $this->headers;
    }
}
