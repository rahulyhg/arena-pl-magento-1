<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCall\Traits\ProductVariantTrait;

class CreateProductVariantImage extends AbstractApiCall implements ApiCallInterface
{
    use ProductVariantTrait;

    /**
     * @param string $url
     *
     * @return self
     *
     * @throws \InvalidArgumentException when empty URL provided
     */
    public function setProductVariantImageUrl($url)
    {
        if (empty($url)) {
            throw new \InvalidArgumentException('Empty URL provided');
        }

        $this->body['url'] = (string) $url;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return ApiCallInterface::METHOD_POST;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        if (!$this->productVariantId) {
            throw new \RuntimeException('Product variant ID not set');
        }

        return sprintf(
            '/api/variants/%d/images/create_from_url',
            $this->productVariantId
        );
    }

    /**
     * Returns image data.
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
