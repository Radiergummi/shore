<?php

namespace Shore\Framework;

use ErrorException;
use LogicException;
use Throwable;

class ErrorHandler
{
    protected const FATAL_ERRORS = E_ERROR | E_USER_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_PARSE;

    /**
     * Whether or not we should silence all errors.
     *
     * @var bool
     */
    protected $silenceErrors = false;

    /**
     * If set to true, will throw all errors as exceptions (making them blocking)
     *
     * @var bool
     */
    protected $throwErrorsAsExceptions = false;

    /**
     * Holds the error handler, if any
     *
     * @var callable|null
     */
    protected $handler;

    /**
     * Holds the error output formatter
     *
     * @var callable
     */
    protected $formatter;

    /**
     * ErrorHandler constructor.
     *
     * @param callable      $formatter
     * @param callable|null $handler
     */
    public function __construct(callable $formatter, callable $handler = null)
    {
        // Let's honor the INI settings.
        if (ini_get('display_errors') == false) {
            $this->silenceAllErrors(true);
        }

        $this->setFormatter($formatter);

        if (! is_null($handler)) {
            $this->setHandler($formatter);
        }
    }

    /**
     * Sets the error handler. This can be used to log errors, for example. The signature of a handler callback *must*
     * be the following: `handler(\Throwable $exception): \Throwable`.
     *
     * @param callable $handler
     */
    public function setHandler(callable $handler): void
    {
        $this->handler = $handler;
    }

    /**
     * Sets the error formatter. This will be used to format an error for user-facing display. The signature of a
     * formatter *must* be the following: `handler(\Throwable $exception): string`.
     *
     * @param callable $formatter
     */
    public function setFormatter(callable $formatter): void
    {
        $this->formatter = $formatter;
    }

    /**
     * Registers the error handler
     */
    public function register(): void
    {
        if (! $this->formatter) {
            throw new LogicException(
                'No formatter has been set before attempting to register the error handler'
            );
        }

        // We control error display at this point
        ini_set('display_errors', false);

        set_error_handler([$this, 'errorHandler']);
        set_exception_handler([$this, 'exceptionHandler']);
        register_shutdown_function([$this, 'shutdownHandler']);
    }

    /**
     * Handles errors from PHP (non-exceptions)
     *
     * @param int    $code
     * @param string $message
     * @param string $file
     * @param int    $line
     *
     * @return bool
     * @throws \ErrorException
     */
    public function errorHandler(int $code, string $message, string $file, int $line)
    {
        // Only handle errors that match the error reporting level.
        if (! ($code & error_reporting())) { // bitwise operation
            if ($code & static::FATAL_ERRORS) {
                $this->terminate();
            }

            return true;
        }

        $exception = new ErrorException($message, 0, $code, $file, $line);

        if ($this->throwErrorsAsExceptions) {
            throw $exception;
        } else {
            $this->exceptionHandler($exception);
        }

        // Fatal errors should be fatal
        if ($code & static::FATAL_ERRORS) {
            $this->terminate();
        }

        return true;
    }

    /**
     * Handles exceptions
     *
     * @param \Throwable $exception
     */
    public function exceptionHandler(Throwable $exception): void
    {
        http_response_code(500);

        $handledException = $this->handle($exception);

        // Only show the error if the output isn't suppressed
        if (! $this->silenceErrors) {
            ob_start();
            $formattedResponse = $this->format($handledException);
            ob_end_clean();

            print $formattedResponse;

            return;
        }
    }

    /**
     * Handles fatal errors via the error handler on shutdown
     */
    public function shutdownHandler(): void
    {
        // We can't throw exceptions in the shutdown handler.
        $this->treatErrorsAsExceptions(false);

        $error = error_get_last();

        if ($error && $error['type'] & static::FATAL_ERRORS) {
            // This won't actually throw, since we disabled that above
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->errorHandler(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        }
    }

    /**
     * Allows the user to explicitly require errors to be thrown as exceptions. This makes all errors blocking, even
     * if they are minor (e.g. E_NOTICE, E_WARNING).
     *
     * @param bool $bool
     */
    public function treatErrorsAsExceptions(bool $bool): void
    {
        $this->throwErrorsAsExceptions = $bool;
    }

    /**
     * Disables the output of error messages
     *
     * @param bool $bool
     */
    public function silenceAllErrors(bool $bool): void
    {
        $this->silenceErrors = $bool;
    }

    /**
     * Terminates the process with a bad error code
     */
    protected function terminate(): void
    {
        exit(1);
    }

    /**
     * Runs the handler callback on the current exception
     *
     * @param \Throwable $exception
     *
     * @return \Throwable
     */
    protected function handle(Throwable $exception): Throwable
    {
        if (! is_callable($this->handler)) {
            return $exception;
        }

        return ($this->handler)($exception);
    }

    /**
     * Runs the formatter callback on the current exception
     *
     * @param \Throwable $exception
     *
     * @return string
     */
    protected function format(Throwable $exception): string
    {
        return ($this->formatter)($exception);
    }
}
