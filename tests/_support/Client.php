<?php

namespace veejay\jsonrpc\tests;

use veejay\jsonrpc\batch\Request;
use veejay\jsonrpc\batch\Response;

/**
 * Class Client
 * @package veejay\jsonrpc\tests
 *
 * @property-read Request[] $requests
 * @property-read Response[] $responses
 */
final class Client extends \veejay\jsonrpc\Client
{
    /**
     * @var array|object|null
     */
    public $_data;

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * {@inheritdoc}
     */
    protected function sendData()
    {
        return $this->_data;
    }
}
