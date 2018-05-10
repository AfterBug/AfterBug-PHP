<?php

namespace AfterBug\Callbacks;

use AfterBug\Config;
use AfterBug\Request\Contracts\RequestInterface;

class Http
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Http constructor.
     *
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Execute environment callback.
     *
     * @param Config $config
     * @return void
     */
    public function __invoke(Config $config)
    {
        $config->setMetaData([
            'request' => [
                'cookies' => $this->request->getCookies(),
                'session' => $this->request->getSession(),
                'headers' => $this->request->getHeaders(),
                'meta' => $this->request->getMetaData(),
            ],
        ]);
    }
}
