<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 28.09.2018
 * Time: 15:07
 */

namespace Shore\Framework\Http;

use Shore\Framework\Application;
use Shore\Framework\Http\Request\Body;
use Shore\Framework\Http\Request\Query;
use Shore\Framework\RequestInterface;

class Request extends Message implements RequestInterface
{
    /**
     * Holds the application instance
     *
     * @var \Shore\Framework\Application
     */
    public $application;

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

    protected $args = [];

    /**
     * Request constructor.
     *
     * @param \Shore\Framework\Application $application
     * @param array                             $server
     * @param array                             $request
     * @param array                             $query
     * @param array                             $body
     * @param array                             $files
     *
     * @throws \Exception If the request body can't be parsed
     */
    public function __construct(
        Application $application,
        array $server,
        array $request,
        array $query,
        array $body,
        array $files
    ) {
        $this->application = $application;
        $this->server = $server;
        $this->headers = getallheaders() ?? [];
        $this->body = new Body($body);
        $this->params = new Query($query);
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
     * Retrieves the current HTTP request method
     *
     * @return string
     */
    public function method(): string
    {
        return $this->server['REQUEST_METHOD'];
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
