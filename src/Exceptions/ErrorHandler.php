<?php

namespace AfterBug\Exceptions;

use AfterBug\Client;
use ErrorException;

class ErrorHandler
{
    /**
     * The client instance.
     *
     * @var Client
     */
    protected $client;

    /**
     * The previously registered error handler.
     *
     * @var callable|null
     */
    protected $oldErrorHandler;

    /**
     * The previously registered exception handler.
     *
     * @var callable|null
     */
    protected $oldExceptionHandler;

    /**
     * Last handled exception.
     *
     * @var \Exception|null
     */
    private $lastHandledException;

    /**
     * @var array
     */
    protected $fatalErrorTypes = array(
        E_ERROR,
        E_PARSE,
        E_CORE_ERROR,
        E_CORE_WARNING,
        E_COMPILE_ERROR,
        E_COMPILE_WARNING,
        E_STRICT,
    );

    /**
     * ErrorHandler constructor.
     *
     * @param Client $client
     */
    protected function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * Register AfterBug handlers.
     *
     * @param Client $client
     * @return static
     */
    public static function register($client)
    {
        $handler = new static($client);

        $handler->registerHandler(true);

        return $handler;
    }

    /**
     * Register our handlers, optionally saving those previously registered.
     *
     * @param  boolean $callExistingErrorHandler
     * @return static
     */
    protected function registerHandler($callExistingErrorHandler)
    {
        $oldError = set_error_handler([$this, 'errorHandler']);
        $oldException = set_exception_handler([$this, 'exceptionHandler']);

        if ($callExistingErrorHandler) {
            $this->oldErrorHandler = $oldError;
            $this->oldExceptionHandler = $oldException;
        }

        register_shutdown_function([$this, 'shutdownHandler']);

        return $this;
    }

    /**
     * Exception handler callback.
     *
     * @param \Exception|\Throwable $exception the exception was was thrown
     */
    public function exceptionHandler($exception)
    {
        $this->lastHandledException = $exception;

        $this->client->catchException($exception);

        if ($this->oldExceptionHandler) {
            call_user_func(
                $this->oldExceptionHandler,
                $exception
            );
        }
    }

    /**
     * Error handler callback.
     *
     * @param int    $code   the level of the error raised
     * @param string $message  the error message
     * @param string $file the filename that the error was raised in
     * @param int    $line the line number the error was raised at
     *
     * @return bool
     */
    public function errorHandler($code, $message, $file, $line)
    {
        switch ($code) {
            case E_NOTICE:
            case E_USER_NOTICE:
                $exception = new Errors\Notice($message, $code, 1, $file, $line);
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $exception = new Errors\Warning($message, $code, 1, $file, $line);
                break;
            case E_ERROR:
            case E_CORE_ERROR:
            case E_RECOVERABLE_ERROR:
            case E_USER_ERROR:
                $exception = new Errors\Fatal($message, $code, 1, $file, $line);
                break;
            default:
                $exception = new Errors\Error($message, $code, 1, $file, $line);
                break;
        }

        $this->exceptionHandler($exception);

        if ($this->oldErrorHandler) {
            return call_user_func(
                $this->oldErrorHandler,
                $code,
                $message,
                $file,
                $line
            );
        }

        return false;
    }

    /**
     * @param int $type
     * @param string|null $message
     * @return bool
     */
    public function shouldCaptureFatalError($type, $message = null)
    {
        if (PHP_VERSION_ID >= 70000 && $this->lastHandledException) {
            if ($type === E_CORE_ERROR && strpos($message, 'Exception thrown without a stack frame') === 0) {
                return false;
            }

            if ($type === E_ERROR) {
                $expectedMessage = 'Uncaught '.get_class($this->lastHandledException).': '.$this->lastHandledException->getMessage();

                if (strpos($message, $expectedMessage) === 0) {
                    return false;
                }
            }
        }

        return (bool) ($type & $this->fatalErrorTypes);
    }

    /**
     * Shutdown handler callback.
     *
     * @return void
     */
    public function shutdownHandler()
    {
        // Get last error
        if (null === $lastError = error_get_last()) {
            return;
        }

        if ($this->shouldCaptureFatalError($lastError['type'], $lastError['message'])) {
            $exception = new ErrorException(
                @$lastError['message'], 0, @$lastError['type'],
                @$lastError['file'], @$lastError['line']
            );

            $this->exceptionHandler($exception);
        }
    }
}
