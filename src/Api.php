<?php

namespace veejay\jsonrpc;

use veejay\jsonrpc\batch\Response;
use veejay\jsonrpc\exception\Exception;

abstract class Api
{
    /**
     * @param string $name
     * @param array $arguments
     * @throws Exception
     */
    public function __call($name, $arguments)
    {
        throw new Exception(Response::METHOD_NOT_FOUND);
    }
}
