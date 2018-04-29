<?php

namespace AfterBug\Callbacks;

use AfterBug\Config;
use AfterBug\Request\Contracts\RequestInterface;

class RequestUser
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
        $config->setUser([
            'id' => $this->request->getRequestIp(),
        ]);
    }
}
