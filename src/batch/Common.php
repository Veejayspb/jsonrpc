<?php

namespace veejay\jsonrpc\batch;

abstract class Common
{
    const VERSION = '2.0';

    /**
     * Jsonrpc version.
     * @var string
     */
    public $jsonrpc;

    /**
     * @var int|string|null
     */
    public $id;

    /**
     * Set properties using arbitrary dataset.
     * @param mixed $data
     * @return static
     */
    public function setProperties($data): self
    {
        foreach ((array)$data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        return $this;
    }
}
