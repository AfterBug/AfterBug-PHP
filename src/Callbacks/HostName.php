<?php

namespace AfterBug\Callbacks;

use AfterBug\Config;

class HostName
{
    /**
     * Execute environment callback.
     *
     * @param Config $config
     * @return void
     */
    public function __invoke(Config $config)
    {
        $config->setMetaData([
            'device' => [
                'hostname' => php_uname('n'),
            ]
        ]);
    }
}
