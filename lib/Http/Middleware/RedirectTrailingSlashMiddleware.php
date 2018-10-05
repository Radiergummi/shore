<?php
/**
 * Created by PhpStorm.
 * User: Moritz
 * Date: 05.10.2018
 * Time: 18:50
 */

namespace Shore\Framework\Http\Middleware;

use Shore\Framework\Facades\Response;
use Shore\Framework\MiddlewareInterface;
use Shore\Framework\RequestHandlerInterface;
use Shore\Framework\RequestInterface;
use Shore\Framework\ResponseInterface;

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
     * @param \Shore\Framework\RequestInterface $request
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
