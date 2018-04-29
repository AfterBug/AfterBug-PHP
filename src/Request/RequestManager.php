<?php

namespace AfterBug\Request;

use AfterBug\Request\Contracts\RequestInterface;

class RequestManager
{
    /**
     * Get Request.
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            return new PhpRequest(
                empty($_SESSION) ? [] : $_SESSION,
                empty($_COOKIE) ? [] : $_COOKIE,
                getallheaders(),
                $_SERVER,
                static::getInputParams($_SERVER, $_POST)
            );
        }

        return new NullRequest();
    }

    /**
     * Get the input params.
     *
     * @param array $server the server variables
     * @param array $post   the post variables
     *
     * @return array|null
     */
    protected static function getInputParams(array $server, array $post)
    {
        static $result;

        if ($result !== null) {
            return $result ?: null;
        }

        $result = $post ?: static::parseInput($server, static::readInput());

        return $result ?: null;
    }

    /**
     * Read the PHP input stream.
     *
     * @return string|false
     */
    protected static function readInput()
    {
        return file_get_contents('php://input') ?: false;
    }

    /**
     * Parse the given input string.
     *
     * @param array       $server the server variables
     * @param string|null $input  the http request input
     *
     * @return array|null
     */
    protected static function parseInput(array $server, $input)
    {
        if (! $input) {
            return null;
        }

        if (isset($server['CONTENT_TYPE']) && stripos($server['CONTENT_TYPE'], 'application/json') === 0) {
            return (array) json_decode($input, true) ?: null;
        }

        if (isset($server['REQUEST_METHOD']) && strtoupper($server['REQUEST_METHOD']) === 'PUT') {
            parse_str($input, $params);

            return (array) $params ?: null;
        }

        return null;
    }
}
