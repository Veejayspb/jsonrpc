<?php

namespace veejay\jsonrpc\tests;

final class Server extends \veejay\jsonrpc\Server
{
    /**
     * Json input data.
     * @var string
     */
    public $_input;

    /**
     * {@inheritdoc}
     */
    protected function getRawContent()
    {
        return $this->_input;
    }
}
