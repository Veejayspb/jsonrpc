<?php

namespace veejay\jsonrpc\tests;

final class Api extends \veejay\jsonrpc\Api
{
    public function withoutParams()
    {
        return 'withoutParams';
    }

    public function withParams($params)
    {
        return 'withParams: ' . implode(', ', $params);
    }

    public function errorMethod()
    {
        return notExists();
    }
}
