<?php

namespace ArenaPl\ApiCall;

class SearchHelper
{
    /**
     * @var string[]
     */
    protected $availableMethods;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $field;

    /**
     * @var scalar
     */
    protected $value;

    /**
     * @param object $object
     *
     * @throws \InvalidArgumentException when param is not an object
     */
    public function __construct($object)
    {
        $this->initAvailableSearchMethods($object);
    }

    /**
     * @param string $field
     *
     * @return self
     *
     * @throws \InvalidArgumentException when empty field provided
     */
    public function setField($field)
    {
        if (empty($field)) {
            throw new \InvalidArgumentException('Empty field provided');
        }

        $this->field = (string) $field;

        return $this;
    }

    /**
     * @param string $method
     *
     * @return self
     *
     * @throws \InvalidArgumentException when search method is not valid
     */
    public function setMethod($method)
    {
        if (!in_array($method, $this->availableMethods, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Search method "%s" is not valid, use one of "%s"',
                $method,
                implode(', ', $this->availableMethods)
            ));
        }

        $this->method = $method;

        return $this;
    }

    /**
     * @param scalar $value
     *
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $this->adjustValueToArenaAPI($value);

        return $this;
    }

    /**
     * @param scalar $value
     *
     * @return scalar
     */
    protected function adjustValueToArenaAPI($value)
    {
        if (null === $value) {
            return '';
        } elseif (true === $value) {
            return 1;
        } elseif (false === $value) {
            return 0;
        } else {
            return $value;
        }
    }

    /**
     * Returns key to include to API call query.
     *
     * @return string
     *
     * @throws \RuntimeException when search field or method is empty
     */
    public function getKey()
    {
        if (empty($this->field) || empty($this->method)) {
            throw new \RuntimeException(sprintf(
                'Search field "%s" or search method "%s" empty',
                $this->field,
                $this->method
            ));
        }

        return sprintf('q[%s_%s]', $this->field, $this->method);
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param object $object
     *
     * @throws \InvalidArgumentException when param is not an object
     */
    protected function initAvailableSearchMethods($object)
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Param is not an object');
        }

        $this->availableMethods = [];

        $reflection = new \ReflectionObject($object);
        foreach ($reflection->getConstants() as $constName => $constValue) {
            if (preg_match('/^SEARCH_METHOD_/i', $constName)) {
                $this->availableMethods[] = $constValue;
            }
        }
    }
}
