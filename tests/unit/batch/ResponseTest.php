<?php

use Codeception\Test\Unit;
use veejay\jsonrpc\batch\Response;

class ResponseTest extends Unit
{
    public function testSetError()
    {
        $response = new Response;
        $original = clone $response;

        $original->error = (object)['code' => Response::INTERNAL_ERROR, 'message' => 'some message'];
        $response->setError(Response::INTERNAL_ERROR, 'some message');
        $this->assertEquals($original, $response);

        $original->error = (object)['code' => Response::METHOD_NOT_FOUND, 'message' => Response::getErrorMessage(Response::METHOD_NOT_FOUND)];
        $self = $response->setError(Response::METHOD_NOT_FOUND);
        $this->assertEquals($original, $response);
        $this->assertEquals($self, $response); // Check that returned the same object
    }

    public function testHasError()
    {
        $response = new Response;
        $this->assertFalse($response->hasError());

        $response->setError(Response::INVALID_PARAMS);
        $this->assertTrue($response->hasError());
    }

    public function testResetError()
    {
        $response = new Response;
        $response->setError(Response::INVALID_PARAMS);
        $response->resetError();
        $this->assertFalse($response->hasError());
    }

    public function testGetData()
    {
        // TODO: ???
    }

    public function testIsNotification()
    {
        $response = new Response;

        $wrong = [true, false, 0, 1, '', 'null', []];
        foreach ($wrong as $value) {
            $response->id = $value;
            $this->assertFalse($response->isNotification());
        }

        $response->id = null;
        $this->assertTrue($response->isNotification());
    }

    public function testGetErrorMessage()
    {
        $message = Response::getErrorMessage(Response::INVALID_PARAMS);
        $this->assertEquals(Response::$errors[Response::INVALID_PARAMS], $message);

        $message = Response::getErrorMessage(1234567890);
        $this->assertEquals(Response::DEFAULT_ERROR, $message);
    }
}
