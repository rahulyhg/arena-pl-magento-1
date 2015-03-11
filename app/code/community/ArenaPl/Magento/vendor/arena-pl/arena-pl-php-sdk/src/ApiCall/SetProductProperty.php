<?php

namespace ArenaPl\ApiCall;

class SetProductProperty extends AbstractProductCall implements ApiCallInterface
{
    /**
     * @var int
     */
    protected $productPropertyId;

    /**
     * @param int $id
     *
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public function setProductPropertyId($id)
    {
        if (!is_numeric($id)) {
            throw new \InvalidArgumentException('Non numeric product property ID provided');
        }

        $this->productPropertyId = (int) $id;

        return $this;
    }

    /**
     * @param mixed $value
     *
     * @return self
     */
    public function setPropertyValue($value)
    {
        $this->body['product_property']['value'] = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return ApiCallInterface::METHOD_PUT;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException when property ID is not set
     */
    public function getPath()
    {
        if (!$this->productPropertyId) {
            throw new \RuntimeException('Property ID not set');
        }

        return $this->buildPath(sprintf(
            '/product_properties/%d',
            $this->productPropertyId
        ));
    }

    /**
     * Returns product property data.
     *
     * @return array
     *
     * @throws ApiCallException
     */
    public function getResult()
    {
        return $this->makeCallJSON();
    }
}
