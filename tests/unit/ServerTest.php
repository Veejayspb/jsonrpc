<?php

use Codeception\Test\Unit;
use veejay\jsonrpc\tests\Api;
use veejay\jsonrpc\tests\ProtectedHelper;
use veejay\jsonrpc\tests\Server;

class ServerTest extends Unit
{
    /**
     * Json input and output.
     * @var array
     * @see https://www.jsonrpc.org/specification
     */
    protected $data = [
        // Standard examples from specification
        '{"jsonrpc":"2.0","method":"withParams","params":[42,23],"id":1}' => '{"jsonrpc":"2.0","result":"withParams: 42, 23","id":1}',
        '{"jsonrpc":"2.0","method":"withParams","params":{"one":1,"two":2},"id":3}' => '{"jsonrpc":"2.0","result":"withParams: 1, 2","id":3}',
        '{"jsonrpc":"2.0","method":"withParams","params":[1,2,3,4,5]}' => '',
        '{"jsonrpc":"2.0","method":"notExists","id":"1"}' => '{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found"},"id":"1"}',
        '{"jsonrpc":"2.0","method":"withoutParams","params":"bar","baz]' => '{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}',
        '{"jsonrpc":"2.0","method":1,"params":"withoutParams"}' => '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid request"},"id":null}',
        '{"jsonrpc":"2.0","method":"withoutParams","params":[1,2,4],"id":"1"},{"jsonrpc":"2.0","method"' => '{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}',
        '[]' => '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid request"},"id":null}',
        '[1]' => '[{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid request"},"id":null}]',
        '[1,2]' => '[{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid request"},"id":null},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid request"},"id":null}]',
        '[{"jsonrpc":"2.0","method":"withParams","params":[1,2],"id":"1"},{"jsonrpc":"2.0","method":"withParams","params":{"i":1}},{"foo":"boo"}]' => '[{"jsonrpc":"2.0","result":"withParams: 1, 2","id":"1"},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid request"},"id":null}]',
        '[{"jsonrpc":"2.0","method":"withParams","params":[1,2]},{"jsonrpc":"2.0","method":"withoutParams"}]' => '',

        // Custom examples
        1 => '{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}',
        true => '{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}',
        false => '{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}',
        'null' => '{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}',
        '[{"jsonrpc":"2.0","method":"withParams","params":[42, 23],"id":1}]' => '[{"jsonrpc":"2.0","result":"withParams: 42, 23","id":1}]',
        '{"jsonrpc":"1.0","method":"withoutParams","id":1}' => '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid request"},"id":1}',
        '{"method":"withoutParams","id":1}' => '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid request"},"id":1}',
        '{"jsonrpc":"2.0","method":["withoutParams"],"params":[42, 23],"id":1}' => '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid request"},"id":1}',
        '{"jsonrpc":"2.0","method":"withParams","params":1,"id":1}' => '{"jsonrpc":"2.0","error":{"code":-32602,"message":"Invalid params"},"id":1}',
        '{"jsonrpc":"2.0","method":"errorMethod","id":1}' => '{"jsonrpc":"2.0","error":{"code":-32603,"message":"Internal error"},"id":1}',
        '[{"jsonrpc":"2.0","method":"withoutParams","id":1},{"jsonrpc":"2.0","method":"withoutParams","id":1}]' => '{"jsonrpc":"2.0","error":{"code":-32001,"message":"Duplicated ID"},"id":null}',
    ];

    public function testRun()
    {
        foreach ($this->data as $input => $output) {
            $server = new Server(new Api);
            $server->_input = $input;
            $result = $server->run();
            $this->assertEquals($output, $result);
        }
    }

    public function testLimit()
    {
        $server = new Server(new Api, 1);
        ProtectedHelper::setProperty($server, 'limit', 2);
        $server->_input = '[{"jsonrpc":"2.0","method":"withoutParams","id":1},{"jsonrpc":"2.0","method":"withoutParams"},{"jsonrpc":"2.0","method":"withoutParams"}]';
        $output = $server->run();
        $this->assertEquals('{"jsonrpc":"2.0","error":{"code":-32000,"message":"Limit exceeded"},"id":null}', $output);
    }
}
