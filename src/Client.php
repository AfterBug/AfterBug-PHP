<?php

namespace AfterBug;

use Exception;
use BadMethodCallException;
use AfterBug\Callbacks\Http;
use League\Pipeline\Pipeline;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\ClientInterface;
use AfterBug\Callbacks\HostName;
use GuzzleHttp\Client as Guzzle;
use AfterBug\Exceptions\Formatter;
use AfterBug\Callbacks\RequestUser;
use AfterBug\Request\RequestManager;
use AfterBug\Request\Contracts\RequestInterface;

class Client
{
    const DEBUG = 'debug';
    const INFO = 'info';
    const WARNING = 'warning';
    const ERROR = 'error';
    const FATAL = 'fatal';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Pipeline
     */
    protected $pipeline;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ClientInterface
     */
    protected $guzzle;

    /**
     * Client constructor.
     *
     * @param Config $config
     * @param ClientInterface|null $guzzle
     * @param RequestInterface|null $request
     */
    public function __construct(Config $config, ClientInterface $guzzle = null, RequestInterface $request = null)
    {
        $this->config = $config;
        $this->pipeline = new Pipeline();
        $this->guzzle = $guzzle ?: static::makeGuzzle($config);
        $this->request = $request ?: (new RequestManager())->getRequest();
    }

    /**
     * Create new AfterBug instance.
     *
     * @param string|null $apiKey
     * @param bool $registerDefaultCallback
     * @return static
     */
    public static function make($apiKey = null, $registerDefaultCallback = true)
    {
        $config = new Config($apiKey ?: getenv('AFTERBUG_API_KEY'));

        $client = new static($config, static::makeGuzzle($config));

        if ($registerDefaultCallback) {
            $client->registerDefaultCallbacks();
        }

        return $client;
    }

    /**
     * Make a new guzzle client instance.
     *
     * @param Config $config
     * @param array $options
     * @return \GuzzleHttp\ClientInterface
     */
    public static function makeGuzzle(Config $config, array $options = [])
    {
        $key = version_compare(ClientInterface::VERSION, '6') === 1 ? 'base_uri' : 'base_url';

        $options = array_merge_recursive(
            $options,
            [
                $key => Config::ENDPOINT,
                'headers' => [
                    'AfterBug-Token' => $config->getApiKey(),
                ],
            ]
        );

        return new Guzzle($options);
    }

    /**
     * Register custom callback.
     *
     * ->registerCallback(function ($config) {
     *      $config->setUser([
     *          'username' => 'alfa',
     *      ]);
     * })
     *
     * @param callable $callback
     * @return $this
     */
    public function registerCallback(callable $callback)
    {
        $this->pipeline
            ->pipe($callback)
            ->process($this->config);

        return $this;
    }

    /**
     * Register default callbacks.
     *
     * @return $this
     */
    public function registerDefaultCallbacks()
    {
        $this->registerCallback(new Http($this->request))
            ->registerCallback(new RequestUser($this->request))
            ->registerCallback(new HostName());

        return $this;
    }

    /**
     * Notify AfterBug of an exception.
     *
     * @param \Exception|\Throwable $exception the exception to notify AfterBug.
     */
    public function catchException($exception)
    {
        $data = Formatter::make($exception, $this->config)->toArray();

        try {
            $this->guzzle->request('POST', '/', [
                RequestOptions::JSON => $data,
            ]);
        } catch (Exception $exception) {
            error_log('AfterBug Error: Couldn\'t notify. '.$exception->getMessage());
        }
    }

    /**
     * Dynamically pass calls to the configuration.
     *
     * @param string $method
     * @param array  $parameters
     * @throws \BadMethodCallException
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $callable = [$this->config, $method];

        if (! is_callable($callable)) {
            throw new BadMethodCallException("Method '{$method}' does not exist.");
        }

        $value = call_user_func_array($callable, $parameters);

        return stripos($method, 'set') === 0 ? $this : $value;
    }
}
