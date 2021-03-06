<?php

namespace AfterBug;

class Config
{
    /**
     * The default endpoint.
     *
     * @var string
     */
    const ENDPOINT = 'https://notify.afterbug.net';

    /**
     * The AfterBug API Key.
     *
     * @var string
     */
    protected $apiKey;

    /**
     * @var array
     */
    protected $user;

    /**
     * @var string
     */
    protected $environment;

    /**
     * The associated meta data.
     *
     * @var array[]
     */
    protected $metaData = [];

    /**
     * @var array[]
     */
    protected $applicationPaths;

    /**
     * @var array
     */
    protected $userAttributes = ['id', 'name', 'email'];

    /**
     * @var array
     */
    protected $excludeExceptions = [];

    /**
     * The notifier to report.
     *
     * @var array
     */
    protected $sdk = [
        'name' => 'AfterBug PHP',
        'version' => '1.0.2',
    ];

    /**
     * Config constructor.
     *
     * @param string|null $apiKey
     */
    public function __construct($apiKey = null)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Get AfterBug API Key.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set SDK.
     *
     * @param array $sdk
     * @return $this
     */
    public function setSdk(array $sdk)
    {
        $this->sdk = $sdk;

        return $this;
    }

    /**
     * @return array
     */
    public function getSdk()
    {
        return $this->sdk;
    }

    /**
     * Set user attributes that will be send to AfterBug server.
     *
     * @param array $attributes
     * @return $this
     */
    public function setUserAttributes(array $attributes)
    {
        $this->userAttributes = $attributes;

        return $this;
    }

    /**
     * Get user attributes that will be send to AfterBug server.
     *
     * @return array
     */
    private function getUserAttributes()
    {
        return (array) $this->userAttributes;
    }

    /**
     * Set user data.
     *
     * @param  array $user
     * @return $this
     */
    public function setUser(array $user)
    {
        $this->user = array_intersect_key(
            $user,
            array_flip($this->getUserAttributes())
        );

        return $this;
    }

    /**
     * Get user data.
     *
     * @return array
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set Meta Data.
     *
     * @param array $meta
     * @return $this
     */
    public function setMetaData(array $meta)
    {
        $this->metaData = array_merge_recursive(
            $this->metaData,
            $meta
        );

        return $this;
    }

    /**
     * @return array[]
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * Return the application paths.
     *
     * @return array
     */
    public function getApplicationPaths()
    {
        return $this->applicationPaths;
    }

    /**
     * Set the application paths.
     *
     * @param array $applicationPaths
     * @return $this
     */
    public function setApplicationPaths($applicationPaths)
    {
        $this->applicationPaths = $applicationPaths;

        return $this;
    }

    /**
     * @param string $environment
     * @return $this
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment ?: 'local';
    }

    /**
     * Set exceptions to exclude.
     *
     * @param array $exceptions
     * @return $this
     */
    public function setExcludeExceptions(array $exceptions)
    {
        $this->excludeExceptions = $exceptions;

        return $this;
    }

    /**
     * Get exclude exceptions.
     *
     * @return array
     */
    public function getExcludeExceptions()
    {
        return $this->excludeExceptions;
    }
}
