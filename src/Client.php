<?php

namespace veejay\jsonrpc;

use veejay\jsonrpc\batch\Request;
use veejay\jsonrpc\batch\Response;

class Client
{
    /**
     * @var string
     */
    protected $uri;

    /**
     * List of requests.
     * @var Request[]
     */
    protected $requests = [];

    /**
     * List of responses.
     * Key - response ID
     * @var Response[]
     */
    protected $responses = [];

    /**
     * @param string $uri
     */
    public function __construct(string $uri)
    {
        $this->uri = $uri;
    }

    /**
     * Add query.
     * @param string $method
     * @param array $params
     * @return Response
     */
    public function query(string $method, array $params = []): Response
    {
        $id = count($this->requests);

        // Create Request
        $this->requests[] = (new Request)->setProperties([
            'jsonrpc' => Request::VERSION,
            'method' => $method,
            'params' => $params,
            'id' => $id,
        ]);

        // Create Response
        $response = $this->responses[$id] = new Response;
        $response->setProperties([
            'jsonrpc' => Request::VERSION,
            'id' => $id,
        ]);
        $response->setError(Response::PARSE_ERROR);
        return $response;
    }

    /**
     * Add notification.
     * @param string $method
     * @param array $params
     * @return void
     */
    public function notify(string $method, array $params = [])
    {
        $request = new Request;
        $request->setProperties([
            'jsonrpc' => Request::VERSION,
            'method' => $method,
            'params' => $params,
        ]);
        $this->requests[] = $request;
    }

    /**
     * @return void
     */
    public function send()
    {
        $data = $this->sendData();
        if ($data === null) return;
        
        if (isset($data->error->code)) {
            $this->setResponsesError($data->error->code, (string)$data->error->message);
            return;
        }

        if (is_object($data)) {
            $data = [$data];
        }

        foreach ($data as $datum) {
            if (!is_object($datum) || !property_exists($datum, 'id')) continue;
            $response = array_key_exists($datum->id, $this->responses) ? $this->responses[$datum->id] : null;
            if (!$response) continue;
            $response->resetError();
            $response->setProperties($datum);
        }
    }

    /**
     * Set errors for all responses.
     * @param int $code
     * @param string $message
     * @return void
     */
    protected function setResponsesError(int $code, string $message = '')
    {
        foreach ($this->responses as $response) {
            $response->setError($code, $message);
        }
    }

    /**
     * Send data on server and receive the response.
     * @return array|object|null - NULL if error occurred
     */
    protected function sendData()
    {
        $content = json_encode($this->requests);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $this->getHeaders($content)),
                'content' => $content,
            ],
        ]);

        $content = file_get_contents($this->uri, false, $context);
        return json_decode($content);
    }

    /**
     * Get headers for request.
     * @param string $content
     * @return array
     */
    private function getHeaders(string $content): array
    {
        return [
            'Accept: application/json',
            'Content-Type: application/json',
            'Connection: close',
            'Content-Length: ' . strlen($content),
        ];
    }
}
