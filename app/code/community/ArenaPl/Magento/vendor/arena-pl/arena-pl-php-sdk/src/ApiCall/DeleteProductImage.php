<?php

namespace ArenaPl\ApiCall;

class DeleteProductImage extends AbstractProductCall implements ApiCallInterface
{
    /**
     * @var int
     */
    protected $productImageId;

    /**
     * @param int $id
     *
     * @return self
     *
     * @throws \InvalidArgumentException when non numeric product image ID provided
     */
    public function setProductImageId($id)
    {
        if (!is_numeric($id)) {
            throw new \InvalidArgumentException('Non numeric product image ID provided');
        }

        $this->productImageId = (int) $id;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return ApiCallInterface::METHOD_DELETE;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException when product image ID is not set
     */
    public function getPath()
    {
        if (!$this->productImageId) {
            throw new \RuntimeException('Product image ID not set');
        }

        return $this->buildPath(sprintf(
            '/images/%d',
            $this->productImageId
        ));
    }

    /**
     * Returns image data.
     *
     * @return bool
     *
     * @throws ApiCallException
     */
    public function getResult()
    {
        return $this->makeCall(204);
    }
}
