<?php

namespace ArenaPl\Test;

trait ReflectionToolsTrait
{
    /**
     * @param object $object
     * @param string $propertyName
     *
     * @return mixed
     */
    protected function getNonPublicObjectProperty($object, $propertyName)
    {
        $reflectionObject = new \ReflectionObject($object);
        $property = $reflectionObject->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * @param object $object
     * @param string $propertyName
     * @param mixed  $propertyValue
     */
    protected function setNonPublicObjectProperty($object, $propertyName, $propertyValue)
    {
        $reflectionObject = new \ReflectionObject($object);
        $property = $reflectionObject->getProperty($propertyName);
        $property->setAccessible(true);

        $property->setValue($object, $propertyValue);
    }

    /**
     * @param object $object
     * @param string $methodName
     *
     * @return mixed
     */
    protected function invokeNonPublicObjectMethod($object, $methodName)
    {
        $method = new \ReflectionMethod($object, $methodName);
        $method->setAccessible(true);

        $funcArgs = func_get_args();
        unset($funcArgs[0], $funcArgs[1]);

        return $method->invokeArgs($object, $funcArgs);
    }
}
