<?php

namespace veejay\jsonrpc\tests;

use Exception;
use ReflectionClass;

final class ProtectedHelper
{
    /**
     * Get protected property.
     * @param string|object $object
     * @param string $property
     * @return mixed|bool
     */
    public static function getProperty($object, string $property)
    {
        $reflection = new ReflectionClass($object);
        if (!$reflection->hasProperty($property)) return false;
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    /**
     * Set protected property.
     * @param string|object $object
     * @param string $property
     * @param mixed $value
     * @return void
     */
    public static function setProperty($object, string $property, $value)
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        if ($property->isStatic()) {
            $property->setValue($value);
        } else {
            $property->setValue($object, $value);
        }
    }

    /**
     * Call protected method.
     * @param string|object $object
     * @param string $methodName
     * @param array $params
     * @return mixed|bool
     */
    public static function callMethod($object, string $methodName, array $params = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        if (!$reflection->hasMethod($methodName)) return false;
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $params);
    }

    /**
     * Catch exception and return the code.
     * @param callable $callback
     * @return int
     */
    public static function catchExceptionCode(callable $callback): int
    {
        try {
            $callback();
            return 0;
        } catch (Exception $e) {
            return $e->getCode();
        }
    }
}
