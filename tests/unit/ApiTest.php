<?php

use Codeception\Test\Unit;
use veejay\jsonrpc\batch\Response;
use veejay\jsonrpc\tests\Api;
use veejay\jsonrpc\tests\ProtectedHelper;

class ApiTest extends Unit
{
    public function testCall()
    {
        $api = new Api;

        $code = ProtectedHelper::catchExceptionCode(function () use ($api) {
            $api->withoutParams();
        });
        $this->assertEquals(0, $code);

        $code = ProtectedHelper::catchExceptionCode(function () use ($api) {
            $api->notExisted();
        });
        $this->assertEquals(Response::METHOD_NOT_FOUND, $code);
    }
}
