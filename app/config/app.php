<?php /** @noinspection PhpUnhandledExceptionInspection */

use Shore\Framework\ErrorFormatter;
use Shore\Framework\Http\Middleware\RedirectTrailingSlashMiddleware;
use Shore\Framework\Io\Filesystem;

/**
 * Application configuration
 * =========================
 * This file holds the main configuration for your application. All
 * relevant keys are documented separately.
 */
return [
    /*------------------------------------------------------------------
     * Application timezone
     *------------------------------------------------------------------
     * PHP requires the timezone to be set in order to use any date or
     * time functions. While the timezone is defaulted to UTC, you can
     * pass a custom timezone here.
     * To see a list of supported timezones, please refer to
     * http://php.net/manual/en/timezones.php
     *------------------------------------------------------------------
     */
    'timezone' => 'Europe/Berlin',

    /*-------------------------------------------------------------------
     * Error handling
     *-------------------------------------------------------------------
     * To properly handle and display errors, you can define both here:
     * An error formatter will be invoked to display any errors in the
     * browser, an error handler will independently receive the exception
     * instance to perform logging or related tasks.
     *-------------------------------------------------------------------
     */
    'errors' => [
        'formatter' => new ErrorFormatter(),
    ],

    /*-------------------------------------------------------------------
     * Application middleware stack
     *-------------------------------------------------------------------
     * All middleware in this array will be executed before the request
     * kernel, in order of appearance. Put any middleware instances your
     * app requires here.
     *-------------------------------------------------------------------
     */
    'middleware' => [
        new RedirectTrailingSlashMiddleware(false),
    ],

    /*-------------------------------------------------------------------
     * Filesystem configuration
     *-------------------------------------------------------------------
     * Shore includes a complete filesystem abstraction, allowing for
     * easy access to the local FS. To have certain directories
     * pre-configured, you can create one or more filesystem instances in
     * this array. They will be accessible using the Filesystem facade.
     * All filesystem will be accessible using the ID "disk:<name>",
     * the first filesystem will receive the accessor "filesystem", too.
     *-------------------------------------------------------------------
     */
    'filesystem' => [
        'uploads' => new Filesystem(
            dirname(__DIR__) . '/../public/uploads'
        ),
    ],

    /*-------------------------------------------------------------------
     * Application services
     *-------------------------------------------------------------------
     * Any service passed into this array will be registered on the DI
     * container, therefore available within route handlers. They key
     * will be used as the service name, the value as the service object.
     *-------------------------------------------------------------------
     */
    'services' => [],
];
