<?php

namespace veejay\jsonrpc\exception;

use veejay\jsonrpc\batch\Response;

class Exception extends \Exception
{
    /**
     * @param int $code
     * @param string $message
     */
    public function __construct(int $code, string $message = '')
    {
        $this->code = $code;
        $this->message = $message ?: Response::getErrorMessage($code);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $response = new Response;
        $response->jsonrpc = Response::VERSION;
        $response->setError($this->code, $this->message);
        return json_encode($response->getData());
    }
}
