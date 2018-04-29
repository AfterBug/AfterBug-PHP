<?php

namespace AfterBug\Exceptions;

use AfterBug\Config;
use Whoops\Run as Whoops;

class Formatter
{
    const DEBUG = 'debug';
    const INFO = 'info';
    const WARNING = 'warning';
    const ERROR = 'error';
    const FATAL = 'fatal';

    /**
     * @var \Exception|\Throwable
     */
    private $exception;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param \Exception|\Throwable $exception
     * @param  Config $config
     * @return static
     */
    public static function make($exception, Config $config)
    {
        return new static($exception, $config);
    }

    /**
     * Formatter constructor.
     *
     * @param \Exception|\Throwable $exception
     */
    protected function __construct($exception, Config $config)
    {
        $this->exception = $exception;
        $this->config = $config;
    }

    /**
     * Get Whoops Runner.
     *
     * @return Whoops
     */
    private function getWhoopsRunner()
    {
        $whoops = new Whoops();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);

        return $whoops;
    }

    /**
     * Translate a PHP Error constant into a log level group
     *
     * @param string $severity PHP error constant
     * @return string Log level group
     */
    protected function translateSeverity($severity)
    {
        switch ($severity) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                return self::ERROR;
            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                return self::WARNING;
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT:
                return self::INFO;
        }

        if (PHP_VERSION_ID >= 50300) {
            switch ($severity) {
                case E_DEPRECATED:
                case E_USER_DEPRECATED:
                    return self::WARNING;
            }
        }

        return self::ERROR;
    }

    /**
     * Format exception as array.
     *
     * @return array
     */
    public function toArray()
    {
        $hasChainedException = PHP_VERSION_ID >= 50300;

        $exception = $this->exception;

        do {
            $whoops = $this->getWhoopsRunner();
            $whoops->pushHandler(new WhoopsHandler($this->config))->register();

            $exceptions[] = json_decode(
                $whoops->handleException($exception),
                true
            );
        } while ($hasChainedException && $exception = $exception->getPrevious());

        $data = [
            'title' => get_class($this->exception),
            'message' => $this->exception->getMessage(),
            'exception' => [
                'file' => $this->exception->getFile(),
                'line' => $this->exception->getLine(),
                'code' => $this->exception->getCode(),
            ],
            'platform' => 'php',
            'environment' => $this->config->getEnvironment(),
            'events' => [
                'exceptions' => $exceptions,
                'meta_data' => $this->config->getMetaData(),
                'user' => $this->config->getUser(),
                'sdk' => $this->config->getSdk(),
            ],
        ];

        if (empty($data['level'])) {
            if (method_exists($exception, 'getSeverity')) {
                $data['level'] = $this->translateSeverity($exception->getSeverity());
            } else {
                $data['level'] = self::ERROR;
            }
        }

        return $data;
    }
}
