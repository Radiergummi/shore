<?php

namespace Shore\Framework\Http;

use InvalidArgumentException;
use Shore\Framework\Specifications\MessageInterface;

class Message implements MessageInterface
{
    /**
     * Holds all request headers
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Holds the body instance
     *
     * @var \Shore\Framework\Http\Request\Body
     */
    protected $body;

    /**
     * Adds the specified message body. The body doesn't need to have a specific type, as string conversion is left to
     * the dispatcher.
     *
     * @param mixed $body Body data
     *
     * @return \Shore\Framework\MessageInterface
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody($body): MessageInterface
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Gets the body of the message.
     *
     * @return string Returns the body as a string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Replaces the specified header if it exists.
     * While header names are case-insensitive, the casing of the header will be preserved by this function, and
     * returned from getHeaders().
     *
     * @param string          $name  Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     *
     * @return \Shore\Framework\MessageInterface
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader(string $name, string $value): MessageInterface
    {
        $this->headers[strtolower($name)] = [$value];

        return $this;
    }

    /**
     * Replaces the specified headers if they exist.
     * While header names are case-insensitive, the casing of the header will be preserved by this function, and
     * returned from getHeaders().
     *
     * @param string[] $headers Array of case-insensitive header field name.
     *
     * @return \Shore\Framework\MessageInterface
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeaders(array $headers = []): MessageInterface
    {
        foreach ($headers as $name => $value) {
            $this->withHeader($name, $value);
        }

        return $this;
    }

    /**
     * Appends the specified value to the given header.
     * Existing values for the specified header will be maintained. The new value(s) will be appended to the existing
     * list. If the header did not exist previously, it will be added.
     *
     * @param string          $name  Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     *
     * @return \Shore\Framework\MessageInterface
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader(string $name, $value): MessageInterface
    {
        if (! is_string($value) || ! is_array($value)) {
            throw new InvalidArgumentException('Invalid header value');
        }

        // If the header exists and the new value is a single string, call withHeader()
        if (! $this->hasHeader($name) && is_string($value)) {
            return $this->withHeader($name, $value);
        }

        $headerName = strtolower($name);

        // Merge the old and new values, casting single strings to an array if necessary
        $this->headers[$headerName] = array_merge(
            $this->headers[$headerName],
            (array) $value
        );

        return $this;
    }

    /**
     * Removes a header field without case-sensitivity.
     *
     * @param string $name Case-insensitive header field name to remove.
     *
     * @return \Shore\Framework\MessageInterface
     */
    public function withoutHeader(string $name): MessageInterface
    {
        unset($this->headers[strtolower($name)]);

        return $this;
    }

    /**
     * Retrieves all message header values.
     * The keys represent the header name as it will be sent over the wire, and each value is an array of strings
     * associated with the header.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             header(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While header names are not case-sensitive, getHeaders() will preserve the exact case in which headers were
     * originally specified.
     *
     * @return string[][] Returns an associative array of the message's headers. Each key MUST be a header name, and
     *                    each value MUST be an array of strings for that header.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return bool Returns true if any header names match the given header name using a case-insensitive string
     *              comparison. Returns false if no matching header name is found in the message.
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     * This method returns an array of all the header values of the given case-insensitive header name.
     * If the header does not appear in the message, this method MUST return an empty array.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string[] An array of string values as provided for the given header. If the header does not appear in
     *                  the message, this method MUST return an empty array.
     */
    public function getHeader(string $name): array
    {
        if (! $this->hasHeader($name)) {
            return [];
        }

        return $this->headers[strtolower($name)];
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     * This method returns all of the header values of the given case-insensitive header name as a string concatenated
     * together using a comma.
     *
     * NOTE: Not all header values may be appropriately represented using comma concatenation. For such headers, use
     * getHeader() instead and supply your own delimiter when concatenating.
     *
     * If the header does not appear in the message, this method MUST return an empty string.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return string A string of values as provided for the given header concatenated together using a comma. If the
     *                header does not appear in the message, this method MUST return an empty string.
     */
    public function getHeaderLine(string $name): string
    {
        if (! $this->hasHeader($name)) {
            return '';
        }

        return implode(',', $this->getHeader($name));
    }
}
