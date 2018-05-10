<?php

namespace AfterBug\Request;

use AfterBug\Request\Contracts\RequestInterface;

class PhpRequest implements RequestInterface
{
    /**
     * The session variables.
     *
     * @var array
     */
    protected $session;

    /**
     * The cookie variables.
     *
     * @var array
     */
    protected $cookies;

    /**
     * The http headers.
     *
     * @var array
     */
    protected $headers;

    /**
     * Server variables.
     *
     * @var array
     */
    protected $server;

    /**
     * The input params.
     *
     * @var array|null
     */
    protected $input;

    /**
     * PhpRequest constructor.
     *
     * @param array $session
     * @param array $cookies
     * @param array $headers
     * @param array $server
     * @param array|null $input
     */
    public function __construct(array $session, array $cookies, array $headers, array $server, array $input = null)
    {
        $this->session = $session;
        $this->cookies = $cookies;
        $this->headers = $headers;
        $this->server = $server;
        $this->input = $input;
    }

    /**
     * Get the session data.
     *
     * @return array
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Get the cookies.
     *
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * Get the headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get server variable.
     *
     * @return array
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Get the request ip.
     *
     * @return string|null
     */
    public function getRequestIp()
    {
        if (isset($this->server['HTTP_X_FORWARDED_FOR'])) {
            return $this->server['HTTP_X_FORWARDED_FOR'];
        }

        if (isset($this->server['REMOTE_ADDR'])) {
            return $this->server['REMOTE_ADDR'];
        }
    }

    /**
     * Get the request url.
     *
     * @return string
     */
    protected function getCurrentUrl()
    {
        $schema = ((! empty($this->server['HTTPS']) && $this->server['HTTPS'] !== 'off') ||
            (! empty($this->server['SERVER_PORT']) && $this->server['SERVER_PORT'] == 443)) ? 'https://' : 'http://';

        $host = isset($this->server['HTTP_HOST']) ? $this->server['HTTP_HOST'] : 'localhost';

        return $schema.$host.$this->server['REQUEST_URI'];
    }

    /**
     * Get the request formatted as meta data.
     *
     * @return array
     */
    public function getMetaData()
    {
        $data = [];

        $data['url'] = $this->getCurrentUrl();

        if (isset($this->server['REQUEST_METHOD'])) {
            $data['method'] = $this->server['REQUEST_METHOD'];
        }

        $data['params'] = $this->input;

        $data['clientIp'] = $this->getRequestIp();

        if (isset($this->server['HTTP_USER_AGENT'])) {
            $data['userAgent'] = $this->server['HTTP_USER_AGENT'];
        }

        return $data;
    }
}
