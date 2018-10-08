<?php

namespace Shore\Framework\Http;

use Shore\Framework\Http\Request\Body;
use Shore\Framework\Http\Request\Query;
use Shore\Framework\Specifications\RequestInterface;

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
     * Holds the request URI
     *
     * @var \Shore\Framework\Http\Uri
     */
    protected $uri;

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
        $this->headers = static::marshalHeaders($server);
        $this->uri = static::marshalUri($server, $this->headers);
        $this->body = $this->withBody(new Body($body));
        $this->params = new Query($query);
    }

    /**
     * Creates a new request from PHP global variables
     *
     * @return \Shore\Framework\Specifications\RequestInterface
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
     * Marshals the URI from the server and header data
     *
     * @param array $server
     * @param array $headers
     *
     * @return \Shore\Framework\Http\Uri
     */
    protected static function marshalUri(array $server, array $headers): Uri
    {
        /**
         * Marshal the host and port from HTTP headers and/or the PHP environment.
         *
         * @param array $headers
         * @param array $server
         *
         * @return array Array of two items, host and port, in that order (can be
         *     passed to a list() operation).
         */
        $marshalHostAndPort = function(array $headers, array $server): array {
            /**
             * @param string|array $host
             *
             * @return array Array of two items, host and port, in that order (can be
             *     passed to a list() operation).
             */
            $marshalHostAndPortFromHeader = function($host) {
                if (is_array($host)) {
                    $host = implode(', ', $host);
                }

                $port = null;

                // works for reg name, IPv4 & IPv6
                if (preg_match('|\:(\d+)$|', $host, $matches)) {
                    $host = substr($host, 0, -1 * (strlen($matches[1]) + 1));
                    $port = (int) $matches[1];
                }

                return [$host, $port];
            };

            /**
             * @param array    $server
             * @param string   $host
             * @param int|null $port
             *
             * @return array Array of two items, host and port, in that order (can be
             *     passed to a list() operation).
             */
            $marshalIpv6HostAndPort = function(array $server, ?int $port): array {
                $host = '[' . $server['SERVER_ADDR'] . ']';
                $port = $port ?: 80;

                if ($port . ']' === substr($host, strrpos($host, ':') + 1)) {
                    // The last digit of the IPv6-Address has been taken as port
                    // Unset the port so the default port can be used
                    $port = null;
                }

                return [$host, $port];
            };

            static $defaults = ['', null];

            $hostHeader = $headers['host'] ?? false;

            if ($hostHeader) {
                return $marshalHostAndPortFromHeader($hostHeader);
            }

            if (! isset($server['SERVER_NAME'])) {
                return $defaults;
            }

            $host = $server['SERVER_NAME'];
            $port = isset($server['SERVER_PORT']) ? (int) $server['SERVER_PORT'] : null;

            if (! isset($server['SERVER_ADDR'])
                || ! preg_match('/^\[[0-9a-fA-F\:]+\]$/', $host)
            ) {
                return [$host, $port];
            }

            // Misinterpreted IPv6-Address
            // Reported for Safari on Windows
            return $marshalIpv6HostAndPort($server, $port);
        };

        /**
         * Detect the path for the request
         *
         * @param array $server
         *
         * @return string
         */
        $marshalRequestPath = function(array $server): string {
            $unencodedUrl = array_key_exists('UNENCODED_URL', $server) ? $server['UNENCODED_URL'] : '';

            if (! empty($unencodedUrl)) {
                return $unencodedUrl;
            }

            $requestUri = array_key_exists('REQUEST_URI', $server) ? $server['REQUEST_URI'] : null;

            if ($requestUri !== null) {
                return preg_replace('#^[^/:]+://[^/]+#', '', $requestUri);
            }

            $origPathInfo = array_key_exists('ORIG_PATH_INFO', $server) ? $server['ORIG_PATH_INFO'] : null;

            if (empty($origPathInfo)) {
                return '/';
            }

            return $origPathInfo;
        };

        $uri = new Uri();

        // URI scheme
        $scheme = 'http';

        $marshalHttpsValue = function($https): bool {
            if (is_bool($https)) {
                return $https;
            }

            return strtolower($https) !== 'off';
        };

        if (array_key_exists('HTTPS', $server)) {
            $https = $marshalHttpsValue($server['HTTPS']);
        } elseif (array_key_exists('https', $server)) {
            $https = $marshalHttpsValue($server['https']);
        } else {
            $https = false;
        }

        if ($https || ($headers['x-forwarded-proto'] ?? false) === 'https') {
            $scheme = 'https';
        }

        $uri->withScheme($scheme);

        // Set the host
        [$host, $port] = $marshalHostAndPort($headers, $server);

        if (! empty($host)) {
            $uri = $uri->withHost($host);

            if (! empty($port)) {
                $uri = $uri->withPort($port);
            }
        }

        // URI path
        $path = $marshalRequestPath($server);

        // Strip query string
        $path = explode('?', $path, 2)[0];

        // URI query
        $query = '';

        if (isset($server['QUERY_STRING'])) {
            $query = ltrim($server['QUERY_STRING'], '?');
        }

        // URI fragment
        $fragment = '';

        if (strpos($path, '#') !== false) {
            [$path, $fragment] = explode('#', $path, 2);
        }

        return $uri
            ->withPath($path)
            ->withFragment($fragment)
            ->withQuery($query);
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
     * @return \Shore\Framework\Http\Uri
     */
    public function uri(): Uri
    {
        return $this->uri;
    }

    /**
     * Sets the request path
     *
     * @param string $path
     *
     * @return \Shore\Framework\Specifications\RequestInterface
     */
    public function withPath(string $path): RequestInterface
    {
        $this->uri->withPath($path);

        return $this;
    }

    public function path(?string $append = ''): string
    {
        return $this->uri()->getPath() . $append;
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
     * @return \Shore\Framework\Specifications\RequestInterface
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
        return $this->headers;
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
        return $this->headers[strtolower($key)] ?? $fallback;
    }
}
