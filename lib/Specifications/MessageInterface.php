<?php

namespace Shore\Framework\Specifications;

/**
 * HTTP message interface
 * ======================
 * This interface closely follows the PSR-7 definition to stay *almost* compatible, except for stream bodies and frozen
 * objects (ie. non-modifiable).
 *
 * HTTP messages consist of requests from a client to a server and responses from a server to a client. This interface
 * defines the methods common to each.
 *
 * @link http://www.ietf.org/rfc/rfc7230.txt
 * @link http://www.ietf.org/rfc/rfc7231.txt
 */
interface MessageInterface
{
    /**
     * Adds the specified message body.
     *
     * The body MUST be a MessageBodyInterface object.
     *
     * @param mixed $body Body data
     *
     * @return \Shore\Framework\Specifications\MessageInterface
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody($body): MessageInterface;

    /**
     * Gets the body of the message.
     *
     * @return mixed Returns the body data
     */
    public function getBody();

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
    public function getHeaders(): array;

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name.
     *
     * @return bool Returns true if any header names match the given header name using a case-insensitive string
     *              comparison. Returns false if no matching header name is found in the message.
     */
    public function hasHeader(string $name): bool;

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
    public function getHeader(string $name): array;

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
    public function getHeaderLine(string $name): string;

    /**
     * Replaces the specified header if it exists.
     * While header names are case-insensitive, the casing of the header will be preserved by this function, and
     * returned from getHeaders().
     *
     * @param string          $name  Case-insensitive header field name.
     * @param string|string[] $value Header value(s).
     *
     * @return \Shore\Framework\Specifications\MessageInterface
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeader(string $name, string $value): MessageInterface;

    /**
     * Replaces the specified headers if they exist.
     * While header names are case-insensitive, the casing of the header will be preserved by this function, and
     * returned from getHeaders().
     *
     * @param string[] $headers Array of case-insensitive header field name.
     *
     * @return \Shore\Framework\Specifications\MessageInterface
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withHeaders(?array $headers = []): MessageInterface;

    /**
     * Appends the specified value to the given header.
     * Existing values for the specified header will be maintained. The new value(s) will be appended to the existing
     * list. If the header did not exist previously, it will be added.
     *
     * @param string          $name  Case-insensitive header field name to add.
     * @param string|string[] $value Header value(s).
     *
     * @return \Shore\Framework\Specifications\MessageInterface
     * @throws \InvalidArgumentException for invalid header names or values.
     */
    public function withAddedHeader(string $name, $value): MessageInterface;

    /**
     * Removes a header field without case-sensitivity.
     *
     * @param string $name Case-insensitive header field name to remove.
     *
     * @return \Shore\Framework\Specifications\MessageInterface
     */
    public function withoutHeader(string $name): MessageInterface;
}
