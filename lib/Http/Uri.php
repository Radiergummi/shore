<?php

namespace Shore\Framework\Http;

use InvalidArgumentException;
use OutOfBoundsException;

class Uri
{
    public const CHAR_SUB_DELIMITERS = '!\$&\'\(\)\*\+,;=';

    public const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~\pL';

    public const STANDARD_PORTS = [
        80,
        443
    ];

    /**
     * Holds the stringified version of the URI
     *
     * @var string
     */
    protected $uriString;

    protected $scheme;

    protected $path;

    protected $host;

    protected $port;

    protected $username;

    protected $password;

    protected $fragment;

    protected $query;

    public function __construct(string $uri = null)
    {
        if (! $uri) {
            return;
        }

        $this->parseUri($uri);
    }

    protected static function normalizeScheme(string $scheme): string
    {
        $scheme = strtolower($scheme);

        $scheme = preg_replace('#:(//)?$#', '', $scheme);

        if ($scheme === '') {
            return '';
        }

        return $scheme;
    }

    protected static function normalizePath(string $path): string
    {
        $path = preg_replace_callback(
            '/(?:[^' . static::CHAR_UNRESERVED . ')(:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/u',
            [static::class, 'urlEncodeChar'],
            $path
        );

        if ($path === '') {
            // No path
            return $path;
        }

        if ($path[0] !== '/') {
            // Relative path
            return $path;
        }

        // Ensure only one leading slash, to prevent XSS attempts.
        return '/' . ltrim($path, '/');
    }

    protected static function normalizeFragment(string $fragment): string
    {
        if (
            $fragment !== '' &&
            strpos($fragment, '#') === 0
        ) {
            $fragment = '%23' . substr($fragment, 1);
        }

        return static::normalizeQueryOrFragment($fragment);
    }

    protected static function normalizeQuery(string $query): string
    {
        if (
            $query !== '' &&
            strpos($query, '?') === 0
        ) {
            $query = substr($query, 1);
        }

        $parts = explode('&', $query);

        foreach ($parts as $index => $part) {
            [$key, $value] = static::splitQueryValue($part);

            if ($value === null) {
                $parts[$index] = static::normalizeQueryOrFragment($key);

                continue;
            }

            $parts[$index] = sprintf(
                '%s=%s',
                static::normalizeQueryOrFragment($key),
                static::normalizeQueryOrFragment($value)
            );
        }

        return implode('&', $parts);
    }

    protected static function normalizeQueryOrFragment(string $value): string
    {
        return preg_replace_callback(
            '/(?:[^' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMITERS . '%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/u',
            [static::class, 'urlEncodeChar'],
            $value
        );
    }

    protected static function normalizeCredential(string $credential): string
    {
        // Note the addition of `%` to initial charset; this allows `|` portion
        // to match and thus prevent double-encoding.
        return preg_replace_callback(
            '/(?:[^%' . self::CHAR_UNRESERVED . self::CHAR_SUB_DELIMITERS . ']+|%(?![A-Fa-f0-9]{2}))/u',
            [static::class, 'urlEncodeChar'],
            $credential
        );
    }

    protected static function splitQueryValue(string $value): array
    {
        $data = explode('=', $value, 2);

        if (! isset($data[1])) {
            $data[] = null;
        }

        return $data;
    }

    protected static function urlEncodeChar(array $matches): string
    {
        return rawurlencode($matches[0]);
    }

    protected static function buildUri(
        string $scheme,
        string $remote,
        string $path,
        string $query,
        string $fragment
    ): string {
        $uri = '';

        if ($scheme !== '') {
            $uri .= sprintf('%s:', $scheme);
        }

        if ($remote !== '') {
            $uri .= '//' . $remote;
        }

        if ($path !== '' && substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }

        $uri .= $path;

        if ($query !== '') {
            $uri .= sprintf('?%s', $query);
        }

        if ($fragment !== '') {
            $uri .= sprintf('#%s', $fragment);
        }

        return $uri;
    }

    public function hasNonStandardPort(): bool
    {
        return (! $this->port || ! in_array($this->port, static::STANDARD_PORTS));
    }

    public function getRemote(): string
    {
        if (! $host = $this->getHost()) {
            return '';
        }

        $remote = $host;

        if ($credentials = $this->getCredentials()) {
            $remote = implode(':', array_values($credentials)) . '@' . $remote;
        }

        if ($this->hasNonStandardPort()) {
            $remote .= ':' . $this->getPort();
        }

        return $remote;
    }

    public function getCredentials(): array
    {
        $credentials = [];

        if ($this->username) {
            $credentials['username'] = $this->username;
        }

        if ($this->password) {
            $credentials['password'] = $this->password;
        }

        return $credentials;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function withScheme(string $scheme): Uri
    {
        if ($scheme === $this->scheme) {
            return $this;
        }

        $this->scheme = static::normalizeScheme($scheme);

        return $this;
    }

    public function withCredentials(string $username, string $password = null): Uri
    {
        $this->username = static::normalizeCredential($username);

        if ($password) {
            $this->password = static::normalizeCredential($password);
        }

        return $this;
    }

    public function withHost(string $host): Uri
    {
        if ($host === $this->host) {
            return $this;
        }

        $this->host = strtolower($host);

        return $this;
    }

    public function withPort(int $port): Uri
    {
        if ($port < 1 || $port > 65535) {
            throw new OutOfBoundsException("Invalid port $port; port must be a valid TCP/UDP port");
        }

        $this->port = $port;

        return $this;
    }

    public function withPath(string $path)
    {
        if (strpos($path, '?')) {
            throw new InvalidArgumentException("Invalid path $path; path must not contain a query string");
        }

        if (strpos($path, '#')) {
            throw new InvalidArgumentException("Invalid path $path; path must not contain a fragment identifier");
        }

        if ($path === $this->path) {
            return $this;
        }

        $this->path = static::normalizePath($path);

        return $this;
    }

    public function withFragment(string $fragment): Uri
    {
        if ($fragment === $this->fragment) {
            return $this;
        }

        $this->fragment = static::normalizeFragment($fragment);

        return $this;
    }

    public function withQuery(string $query): Uri
    {
        if (strpos($query, '#') !== false) {
            throw new InvalidArgumentException('Query string must not include a URI fragment identifier');
        }

        if ($query === $this->query) {
            return $this;
        }

        $this->query = static::normalizeQuery($query);

        return $this;
    }

    public function __toString(): string
    {
        if (! $this->uriString) {
            $this->uriString = static::buildUri(
                $this->getScheme(),
                $this->getRemote(),
                $this->getPath(),
                $this->getQuery(),
                $this->getFragment()
            );
        }

        return $this->uriString;
    }

    /**
     * Parses the supplied URI
     *
     * @param string $uri
     */
    protected function parseUri(string $uri): void
    {
        $parts = parse_url($uri);

        if ($parts === false) {
            throw new InvalidArgumentException('The source URI string appears to be malformed');
        }

        $this->scheme = isset($parts['scheme']) ? static::normalizeScheme($parts['scheme']) : '';
        $this->username = isset($parts['user']) ? static::normalizeCredential($parts['user']) : '';
        $this->host = isset($parts['host']) ? strtolower($parts['host']) : '';
        $this->port = isset($parts['port']) ? $parts['port'] : null;
        $this->path = isset($parts['path']) ? static::normalizePath($parts['path']) : '';
        $this->query = isset($parts['query']) ? static::normalizeQuery($parts['query']) : '';
        $this->fragment = isset($parts['fragment']) ? static::normalizeFragment($parts['fragment']) : '';

        if (isset($parts['pass'])) {
            $this->password .= ':' . static::normalizeCredential($parts['pass']);
        }
    }
}
