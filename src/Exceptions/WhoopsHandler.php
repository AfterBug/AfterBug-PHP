<?php

namespace AfterBug\Exceptions;

use AfterBug\Config;
use Whoops\Util\Misc;
use Whoops\Handler\Handler;

class WhoopsHandler extends Handler
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * WhoopsHandler constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get the code of the exception that is currently being handled.
     *
     * @return string
     */
    protected function getExceptionCode()
    {
        $exception = $this->getException();

        $code = $exception->getCode();
        if ($exception instanceof \ErrorException) {
            // ErrorExceptions wrap the php-error types within the 'severity' property
            $code = Misc::translateErrorCode($exception->getSeverity());
        }

        return (string) $code;
    }

    /**
     * Get the stack trace frames of the exception that is currently being handled.
     *
     * @return \Whoops\Exception\FrameCollection;
     */
    protected function getExceptionFrames()
    {
        $frames = $this->getInspector()->getFrames();

        if ($this->config->getApplicationPaths()) {
            foreach ($frames as $frame) {
                foreach ($this->config->getApplicationPaths() as $path) {
                    if (strpos($frame->getFile(), $path) === 0) {
                        $frame->setApplication(true);
                        break;
                    }
                }
            }
        }

        return $frames;
    }

    /**
     * @return int|null A handler may return nothing, or a Handler::HANDLE_* constant
     */
    public function handle()
    {
        $inspector = $this->getInspector();

        $frames = $this->getExceptionFrames();

        $data = [
            'type' => $inspector->getExceptionName(),
            'message' => $inspector->getExceptionMessage(),
            'file' => $inspector->getException()->getFile(),
            'line' => $inspector->getException()->getLine(),
            'code' => $this->getExceptionCode(),
            'count_is_application' => $frames->countIsApplication(),
            'stack_traces' => (new StackTrace($frames))->toArray(),
        ];

        echo json_encode($data);

        return Handler::QUIT;
    }
}
