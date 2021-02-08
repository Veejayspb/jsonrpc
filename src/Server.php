<?php

namespace veejay\jsonrpc;

use Error;
use veejay\jsonrpc\batch\Request;
use veejay\jsonrpc\batch\Response;
use veejay\jsonrpc\exception\Exception;

class Server
{
    /**
     * Api for responses.
     * @var Api
     */
    protected $api;

    /**
     * Max amount of requests.
     * 0 - unlimited.
     * @var int|null
     */
    protected $limit;

    /**
     * @param Api $api
     * @param int $limit
     */
    public function __construct(Api $api, int $limit = 0)
    {
        $this->api = $api;
        $this->limit = $limit;
    }

    /**
     * Run server.
     * @return string
     */
    public function run()
    {
        try {
            $data = $this->getData();
            $multiple = is_array($data);
            if (is_object($data)) {
                $data = [$data];
            }

            if (empty($data)) {
                throw new Exception(Response::INVALID_REQUEST);
            } elseif ($this->limit !== 0 && $this->limit < count($data)) {
                throw new Exception(Response::LIMIT_EXCEEDED);
            }

            $responses = [];
            foreach ((array)$data as $datum) {
                $request = (new Request)->setProperties($datum);
                $response = $this->getResponse($request);
                if ($response->isNotification() && !$response->hasError()) {
                    continue;
                }
                $responses[] = $response->getData();
            }

            // Check if ID already exists
            $ids = [];
            foreach ($responses as $response) {
                if ($response->id === null) continue;
                if (in_array($response->id, $ids, true)) {
                    throw new Exception(Response::DUPLICATED_ID);
                }
                array_push($ids, $response->id);
            }

        } catch (Exception $exception) {
            return (string)$exception;
        }

        if (empty($responses)) return '';
        return json_encode($multiple ? $responses : current($responses));
    }

    /**
     * Create Response object for Request.
     * @param Request $request
     * @return Response
     */
    protected function getResponse(Request $request): Response
    {
        $response = new Response;
        $response->jsonrpc = Response::VERSION;
        $response->id = $request->id;

        $this->validate($request, $response);
        if ($response->hasError()) {
            return $response;
        }

        try {
            $response->result = call_user_func_array([$this->api, $request->method], [(array)$request->params]);
        } catch (Exception $e) {
            $response->setError($e->getCode(), $e->getMessage());
        } catch (Error $e) {
            $response->setError(Response::INTERNAL_ERROR);
        }

        return $response;
    }

    /**
     * Validate Request and set error.
     * @param Request $request
     * @param Response $response
     * @return void
     */
    protected function validate(Request $request, Response $response)
    {
        $type = gettype($request->id);
        if (
            $request->jsonrpc !== Response::VERSION ||
            !is_string($request->method) ||
            !in_array($type, ['NULL', 'integer', 'string'])
        ) {
            $response->setError(Response::INVALID_REQUEST);
        } elseif (!is_array($request->params) && !is_object($request->params) && !is_null($request->params)) {
            $response->setError(Response::INVALID_PARAMS);
        }
    }

    /**
     * Get data from request.
     * @return mixed
     * @throws Exception
     */
    protected function getData()
    {
        $content = $this->getRawContent();
        $data = is_string($content) ? @json_decode($content) : null;
        if ($data === null) {
            throw new Exception(Response::PARSE_ERROR);
        }
        return $data;
    }

    /**
     * Read raw content.
     * @return string|false
     */
    protected function getRawContent()
    {
        return @file_get_contents('php://input');
    }
}
