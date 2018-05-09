<?php

namespace AfterBug\Exceptions;

use Whoops\Exception\Frame;
use Whoops\Exception\FrameCollection;

class StackTrace
{
    /**
     * @var FrameCollection
     */
    protected $frames;

    /**
     * StackTrace constructor.
     *
     * @param FrameCollection $frames
     */
    public function __construct(FrameCollection $frames)
    {
        $this->frames = $frames;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $traces = [];

        foreach ($this->frames as $frame) {
            $lineStart = 0;

            /** @var Frame $frame */
            $line = $frame->getLine();
            $codeChunk = null;

            if ($line !== null) {
                $codeChunk = $frame->getFileLines($line - 10, 20);

                if ($codeChunk) {
                    $codeChunk = array_map(function ($line) {
                        return empty($line) ? ' ' : $line;
                    }, $codeChunk);

                    $lineStart = key($codeChunk) + 1;
                }
            }

            $traces[] = [
                'line' => $line,
                'file' => $frame->getFile(true),
                'class' => $frame->getClass(),
                'function' => $frame->getFunction(),
                'in_app' => $frame->isApplication(),
                'context' => $codeChunk,
                'line_start' => $lineStart,
            ];
        }

        return $traces;
    }
}
