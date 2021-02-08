<?php

namespace veejay\jsonrpc\batch;

class Request extends Common
{
    /**
     * @var string
     */
    public $method;

    /**
     * @var array|object
     */
    public $params = [];
}
