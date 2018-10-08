<?php

namespace Shore\Framework\Http\Middleware;

use Shore\Framework\Facades\Response;
use Shore\Framework\Specifications\MiddlewareInterface;
use Shore\Framework\Specifications\RequestHandlerInterface;
use Shore\Framework\Specifications\RequestInterface;
use Shore\Framework\Specifications\ResponseInterface;

class RedirectTrailingSlashMiddleware implements MiddlewareInterface
{
    /**
     * Whether to use append or remove trailing slashes
     *
     * @var bool
     */
    protected $useSlash;

    /**
     * RedirectTrailingSlashMiddleware constructor.
     *
     * @param bool $useSlash Whether to use append or remove trailing slashes
     */
    public function __construct(bool $useSlash = false)
    {
        $this->useSlash = $useSlash;
    }

    public function process(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Normalize the path
        $path = $this->normalize($request);

        // If the normalized path differs from the request path, redirect to the target path
        if ($path !== $request->path()) {
            return Response::redirect($path);
        }

        // Yield the request, setting the normalized path
        return $handler->next($request->withPath($path));
    }

    /**
     * Normalizes the request path by adding or removing the trailing slash
     *
     * @param \Shore\Framework\Specifications\RequestInterface $request
     *
     * @return string
     */
    protected function normalize(RequestInterface $request): string
    {
        $path = $request->path();

        if ($path === '') {
            return '/';
        }

        if (strlen($path) > 1) {
            if ($this->useSlash) {
                // check if it's not a root path
                if (substr($path, -1) !== '/') {
                    return $path . '/';
                }
            } else {
                return rtrim($path, '/\\');
            }
        }

        return $path;
    }
}
