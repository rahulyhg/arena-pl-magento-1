<?php

namespace ArenaPl\ApiCall;

class CreateProductImage extends AbstractProductCall implements ApiCallInterface
{
    /**
     * @param string $url
     *
     * @return self
     *
     * @throws \InvalidArgumentException when empty URL provided
     */
    public function setProductImageUrl($url)
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
        return $this->buildPath('/images/create_from_url');
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
