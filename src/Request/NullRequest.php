<?php

namespace AfterBug\Request;

use AfterBug\Request\Contracts\RequestInterface;

class NullRequest implements RequestInterface
{
    /**
     * Get the session data.
     *
     * @return array
     */
    public function getSession()
    {
        return [];
    }

    /**
     * Get the cookies.
     *
     * @return array
     */
    public function getCookies()
    {
        return [];
    }

    /**
     * Get the headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return [];
    }

    /**
     * Get server variable.
     *
     * @return array
     */
    public function getServer()
    {
        return [];
    }

    /**
     * Get the request formatted as meta data.
     *
     * @return array
     */
    public function getMetaData()
    {
        return [];
    }

    /**
     * Get the request ip.
     *
     * @return string|null
     */
    public function getRequestIp()
    {
        return '127.0.0.1';
    }
}
