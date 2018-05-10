<?php

namespace AfterBug\Request\Contracts;

interface RequestInterface
{
    /**
     * Get the session data.
     *
     * @return array
     */
    public function getSession();

    /**
     * Get the cookies.
     *
     * @return array
     */
    public function getCookies();

    /**
     * Get the headers.
     *
     * @return array
     */
    public function getHeaders();

    /**
     * Get server variable.
     *
     * @return array
     */
    public function getServer();

    /**
     * Get the request formatted as meta data.
     *
     * @return array
     */
    public function getMetaData();

    /**
     * Get the request ip.
     *
     * @return string|null
     */
    public function getRequestIp();
}
