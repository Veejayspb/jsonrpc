JSONRPC server and client
=========================
Jsonrpc 2.0 for PHP over HTTP(S).

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Veejayspb/jsonrpc/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Veejayspb/jsonrpc/?branch=master)

Examples
--------

### Server
At first you have to extend **veejay\jsonrpc\Api** class and add required methods:
```php
class MyApi extends Api
{
    public function myMethod(array $params)
    {
        extract($params);
        if (!isset($myParam)) {
            throw new Exception(Response::INVALID_PARAMS);
        }
        return 'some result with param: ' . $myParam;
    }
}
```
Then run **Server** with the following code:
```php
$server = new Server(new MyApi);
echo $response = $server->run();
```

### Client
```php
$client = new Client('https://jsonrpc/server/address');

$query = $client->query('myMethod', ['my_param' => 1]);
$client->notify('myMethod');

$client->send();
```
You will receive the response from **myMethod** in **$query** variable.

Requirements
------------
- PHP 7.2+

Installation
------------
```
composer require "veejay/jsonrpc"
```
