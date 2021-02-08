<?php

use Codeception\Test\Unit;
use veejay\jsonrpc\batch\Request;

class RequestTest extends Unit
{
    public function testSetProperties()
    {
        $expected = new Request;
        $actual = clone $expected;

        // 1st
        $expected->jsonrpc = '2.0';
        $expected->method = 'withoutParams';
        $expected->params = ['s' => 'sss'];
        $expected->id = 1;

        $actual->setProperties([
            'jsonrpc' => '2.0',
            'method' => 'withoutParams',
            'params' => ['s' => 'sss'],
            'id' => 1,
        ]);
        $this->assertEquals($expected, $actual);

        // 2nd
        $expected->method = 'withoutParams';

        $actual = clone $expected;
        $actual->setProperties([
            'method' => 'withoutParams',
            'not_exists_1' => 2,
        ]);
        $this->assertEquals($expected, $actual);

        // 3rd
        $expected->id = null;
        $expected->params = [];

        $actual = clone $expected;
        $actual->setProperties([
            'id' => null,
            'params' => [],
        ]);
        $this->assertEquals($expected, $actual);
    }
}
