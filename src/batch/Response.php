<?php

namespace veejay\jsonrpc\batch;

use stdClass;

class Response extends Common
{
    const PARSE_ERROR = -32700;
    const INVALID_REQUEST = -32600;
    const METHOD_NOT_FOUND = -32601;
    const INVALID_PARAMS = -32602;
    const INTERNAL_ERROR = -32603;
    const LIMIT_EXCEEDED = -32000;
    const DUPLICATED_ID = -32001;

    const DEFAULT_ERROR = 'Server error';

    /**
     * @var mixed
     */
    public $result;

    /**
     * @var stdClass|null
     */
    public $error;

    /**
     * Errors list.
     * @var array
     */
    public static $errors = [
        self::PARSE_ERROR => 'Parse error',
        self::INVALID_REQUEST => 'Invalid request',
        self::METHOD_NOT_FOUND => 'Method not found',
        self::INVALID_PARAMS => 'Invalid params',
        self::INTERNAL_ERROR => 'Internal error',
        self::LIMIT_EXCEEDED => 'Limit exceeded',
        self::DUPLICATED_ID => 'Duplicated ID',
    ];

    /**
     * Set error status to current response.
     * @param int $code
     * @param string $message
     * @return static
     */
    public function setError(int $code, string $message = ''): self
    {
        $error = new stdClass();
        $error->code = $code;
        $error->message = $message ?: static::getErrorMessage($code);
        $this->error = $error;
        return $this;
    }

    /**
     * Check if current response has an error.
     * @return bool
     */
    public function hasError(): bool
    {
        return $this->error !== null;
    }

    /**
     * Reset error.
     * @return void
     */
    public function resetError()
    {
        $this->error = null;
    }

    /**
     * Is request for current response a notification.
     * @return bool
     */
    public function isNotification(): bool
    {
        return $this->id === null;
    }

    /**
     * Return object with prepared data.
     * @return stdClass
     */
    public function getData(): stdClass
    {
        $object = new stdClass;
        $object->jsonrpc = $this->jsonrpc;
        if ($this->hasError()) {
            $object->error = $this->error;
        } else {
            $object->result = $this->result;
        }
        $object->id = $this->id;
        return $object;
    }

    /**
     * Get error message by code.
     * @param int $code
     * @return string
     */
    public static function getErrorMessage(int $code): string
    {
        return array_key_exists($code, static::$errors) ? static::$errors[$code] : static::DEFAULT_ERROR;
    }
}
