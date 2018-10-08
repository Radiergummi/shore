<?php

namespace Shore\Framework\Routing;

use Shore\Framework\Exception\Route\NotFoundException;
use Shore\Framework\Exception\Router\InvalidRequestMethodException;
use Shore\Framework\Specifications\RequestInterface;
use Shore\Framework\Specifications\RouterInterface;

/**
 * Router implementation
 * =====================
 *
 * Routes can be registered by using the methods aptly named after the HTTP request method names (get(), post() etc.).
 * They expect two arguments: The URI expression they should match on and the handler. Route handlers are covered
 * in-depth in the Route class, so take a look there.
 * The URI expressions allow to use a special kind of named placeholders. You can either specify a plain path, like the
 * following: `/foo/bar/baz`. This will only match this exact request URI. Alternatively, you can use the
 * aforementioned
 * placeholders. All placeholders must be enclosed in delimiter characters (defined in the constants
 * `MATCH_DELIMITER_OPENING` and `MATCH_DELIMITER_CLOSING`), thereby providing named parameters. This might look like
 * so: `/foo/{bar}/{baz}`. This route will match any URI consisting of "foo" in the first segment and two further
 * segments, for example `/foo/123/test`. That works because the placeholders are replaced with `([^/]+?)` internally,
 * matching anything except the next slash.
 * For more complex rules, you can optionally specify another replacement regex. To do so, use the following
 * placeholder syntax: `{variable_name:regex}`, so for example `{id:\d+}`. The latter example will only match numeric
 * variables. Just make sure your expressions are valid.
 *
 * @package Shore\Framework\Routing
 */
class Router implements RouterInterface
{
    public const MATCH_DELIMITER_CLOSING = '}';

    public const MATCH_DELIMITER_OPENING = '{';

    public const MATCH_DELIMITER_SEPARATOR = ':';

    public const METHOD_ANY = 'ANY';

    public const METHOD_DELETE = 'DELETE';

    public const METHOD_GET = 'GET';

    public const METHOD_HEAD = 'HEAD';

    public const METHOD_PATCH = 'PATCH';

    public const METHOD_POST = 'POST';

    public const METHOD_PUT = 'PUT';

    /**
     * Holds the routes
     *
     * @var array
     */
    protected $routes = [];

    public function any(string $uri, $handler): void
    {
        $this->register(static::METHOD_ANY, $uri, $handler);
    }

    public function get(string $uri, $handler): void
    {
        $this->register(static::METHOD_GET, $uri, $handler);
    }

    public function post(string $uri, $handler): void
    {
        $this->register(static::METHOD_POST, $uri, $handler);
    }

    public function put(string $uri, $handler): void
    {
        $this->register(static::METHOD_PUT, $uri, $handler);
    }

    public function delete(string $uri, $handler): void
    {
        $this->register(static::METHOD_DELETE, $uri, $handler);
    }

    public function patch(string $uri, $handler): void
    {
        $this->register(static::METHOD_PATCH, $uri, $handler);
    }

    public function head(string $uri, $handler): void
    {
        $this->register(static::METHOD_HEAD, $uri, $handler);
    }

    /**
     * Creates a resource route. This is essentially just a shortcut for doing it by yourself.
     *
     * @param string $name
     * @param string $controllerName
     */
    public function resource(string $name, string $controllerName)
    {
        $this->get("/$name", "$controllerName@index");
        $this->post("/$name", "$controllerName@create");
        $this->get("/$name/{id}", "$controllerName@show");
        $this->put("/$name/{id}", "$controllerName@update");
        $this->delete("/$name/{id}", "$controllerName@destroy");
    }

    /**
     * Matches a request URI against all registered routes. Matching happens incrementally: We first try to find a
     * direct match. If that fails, we'll assume the URI contains placeholders that need to be matched and try to do so.
     * During matching the placeholders, any routes that don't contain placeholders or start with another character
     * than the current request URI will be skipped.
     * Otherwise, we try to match all placeholders in a route definition and replace them with a regex representation.
     * Lastly, we replace the placeholder with its regex and match the current request URI against the whole regex,
     * which allows us to retrieve the first matching route for the given request input.
     * If there is still no match, however, a `NotFoundException` will be thrown, subject to being handled further up
     * the stack.
     * To use this router in a customer-facing application, you might want to implement another way to handle 404's
     * here - maybe via a special 404 route that gets called if no route matches, and avoid throwing at all. It's
     * totally okay for an API, though.
     *
     * @param RequestInterface $request
     *
     * @return \Shore\Framework\Routing\Route
     * @throws \Shore\Framework\Exception\Router\InvalidRequestMethodException
     * @throws \Shore\Framework\Exception\Route\NotFoundException
     */
    public function match(RequestInterface $request): Route
    {
        $requestMethod = $request->method();
        $requestUri = $request->path();

        // If the request method doesn't exist, something seems to be fucked up.
        if (! isset($this->routes[$requestMethod])) {
            throw new InvalidRequestMethodException($requestMethod);
        }

        // Merge the any-method-routes and those matching the current request method
        $routes = array_merge(
            $this->routes[static::METHOD_ANY] ?? [],
            $this->routes[$requestMethod] ?? []
        );

        // Check for direct matches
        if (isset($this->routes[$requestMethod][$requestUri])) {
            $route = $this->routes[$requestMethod][$requestUri];

            return $route;
        }

        // Build the placeholder regex from the delimiter characters
        $placeholderExpression = sprintf(
            '#(?:\%s(.+?)(?:%s.*)?\%s)#',
            static::MATCH_DELIMITER_OPENING,
            static::MATCH_DELIMITER_SEPARATOR,
            static::MATCH_DELIMITER_CLOSING
        );

        /**
         * @var string                         $uri
         * @var \Shore\Framework\Routing\Route $route
         */
        foreach ($routes as $uri => $route) {
            // If the current route doesn't contain any placeholder delimiters, we can skip it
            if (strpos($uri, static::MATCH_DELIMITER_OPENING) === false) {
                continue;
            }

            // If the first character isn't the delimiter of a placeholder and doesn't match the first character
            // of the request URI, continue straight away.
            if (
                strpos($uri, static::MATCH_DELIMITER_OPENING) !== 1 &&
                $uri[1] !== ($requestUri[1] ?? '')
            ) {
                continue;
            }

            // Store the found placeholders
            $placeholders = [];

            // Match all placeholders in the current URI
            preg_match_all(
                $placeholderExpression,
                $uri,
                $placeholders,
                PREG_SET_ORDER
            );

            // Create a copy of the URI (we'll need it later on)
            $expression = $uri;

            // Create a list of placeholder names
            $placeholderNames = [];

            foreach ($placeholders as $item) {
                $replacement = '[^/]+?';

                // Extract full match and partial match from the result
                list($placeholder, $name) = $item;

                // Save the variable name
                $placeholderNames[] = $name;

                // Check if we've got a custom expression (by looking for the separator sequence)
                if (strpos($placeholder, static::MATCH_DELIMITER_SEPARATOR) !== false) {
                    // Variable expression starts after "{<name>:"
                    $offset = strpos($placeholder, $name) + strlen($name) + 1;

                    // Replacement will be anything between the offset and the last character ("}")
                    // This should probably account for the closing delimiter length.
                    $replacement = substr($placeholder, $offset, -1);
                }

                // Replace the full placeholder with a match-anything rule
                $expression = str_replace($placeholder, "($replacement)", $expression);
            }

            // Keep the matched variable values
            $matches = [];

            // Try to match the compiled regular expression against the request URI
            if (preg_match("~^$expression\$~", $requestUri, $matches)) {
                // Remove the useless full matches
                array_shift($matches);

                // Assign the placeholder names to the matched values from the URI
                $variables = array_combine($placeholderNames, $matches);

                // Pass the variables to the route instance
                return $route->withArgs($variables);
            }
        }

        // No routes found, PANIC!
        throw new NotFoundException($requestUri);
    }

    /**
     * Registers a route on the router
     *
     * @param string                                               $method       HTTP request method
     * @param string                                               $uri          HTTP request URI
     * @param callable|\Shore\Framework\ControllerInterface|string $handler      Route handler. May be a callable, a
     *                                                                           controller instance or a fully
     *                                                                           qualified class path
     */
    protected function register(string $method, string $uri, $handler): void
    {
        $route = new Route($uri, $handler);

        if (! isset($this->routes[$method])) {
            $this->routes[$method] = [];
        }

        $this->routes[$method][$route->getUri()] = $route;
    }
}
