<?php

use Codeception\Test\Unit;
use veejay\jsonrpc\tests\Client;
use veejay\jsonrpc\batch\Request;
use veejay\jsonrpc\batch\Response;
use veejay\jsonrpc\tests\ProtectedHelper;

class ClientTest extends Unit
{
    public function testRequests()
    {
        $client = new Client('');
        $values = [];

        $client->query('withoutParams');
        $values[] = (new Request)->setProperties([
            'jsonrpc' => Request::VERSION,
            'method' => 'withoutParams',
            'id' => 0,
        ]);
        $this->assertEquals($values, $client->requests);

        $client->notify('withoutParams');
        $values[] = (new Request)->setProperties([
            'jsonrpc' => Request::VERSION,
            'method' => 'withoutParams',
        ]);
        $this->assertEquals($values, $client->requests);

        $client->query('withoutParams', ['param' => 'one']);
        $values[] = (new Request)->setProperties([
            'jsonrpc' => Request::VERSION,
            'method' => 'withoutParams',
            'params' => ['param' => 'one'],
            'id' => 2,
        ]);
        $this->assertEquals($values, $client->requests);
    }

    public function testResponses()
    {
        $client = new Client('');
        $values = [];

        $client->query('withoutParams');
        $values[0] = (new Response)->setProperties([
            'jsonrpc' => Response::VERSION,
            'id' => 0,
        ])->setError(Response::PARSE_ERROR);
        $this->assertEquals($values, $client->responses);

        $client->notify('withoutParams');
        $this->assertEquals($values, $client->responses);

        $client->query('withoutParams', ['param' => 'one']);
        $values[2] = (new Response)->setProperties([
            'jsonrpc' => Response::VERSION,
            'id' => 2,
        ])->setError(Response::PARSE_ERROR);
        $this->assertEquals($values, $client->responses);
    }

    public function testSend()
    {
        $client = new Client('');
        $actual = $client->query('withoutParams');

        // Error while getting content
        $client->_data = null;
        $client->send();
        $expected = (new Response)->setProperties([
            'jsonrpc' => Response::VERSION,
            'id' => 0,
        ])->setError(Response::PARSE_ERROR);
        $this->assertEquals($expected, $actual);

        // Error response
        $client->_data = json_decode('{"jsonrpc": "2.0", "error": {"code": -32700: "message": "Parse error"}, "id": null}');
        $client->send();
        $expected = (new Response)->setProperties([
            'jsonrpc' => Response::VERSION,
            'id' => 0,
        ])->setError(Response::PARSE_ERROR);
        $this->assertEquals($expected, $actual);

        // Single response
        $client->_data = json_decode('{"jsonrpc": "2.0", "result": "result_str", "id": 0}');
        $client->send();
        $expected = (new Response)->setProperties([
            'jsonrpc' => Response::VERSION,
            'result' => 'result_str',
            'id' => 0,
        ]);
        $this->assertEquals($expected, $actual);

        // Multiple responses
        $client->_data = json_decode('[{"jsonrpc": "2.0", "result": "result1", "id": 0}, {"jsonrpc": "2.0", "result": "result2", "id": 1}]');
        $actual2 = $client->query('withoutParams');
        $client->send();
        $expected = (new Response)->setProperties([
            'jsonrpc' => Response::VERSION,
            'result' => 'result1',
            'id' => 0,
        ]);
        $this->assertEquals($expected, $actual);
        $expected = (new Response)->setProperties([
            'jsonrpc' => Response::VERSION,
            'result' => 'result2',
            'id' => 1,
        ]);
        $this->assertEquals($expected, $actual2);
    }

    public function testSetResponsesError()
    {
        $client = new Client('');
        $one = $client->query('one');
        $two = $client->query('two');

        ProtectedHelper::callMethod($client, 'setResponsesError', [Response::INTERNAL_ERROR, 'qqq']);
        $this->assertEquals(
            ['code' => Response::INTERNAL_ERROR, 'message' => 'qqq'],
            (array)$one->error
        );
        $this->assertEquals(
            ['code' => Response::INTERNAL_ERROR, 'message' => 'qqq'],
            (array)$two->error
        );
    }
}
