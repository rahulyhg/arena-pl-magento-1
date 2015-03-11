<?php

namespace ArenaPl\ApiCall;

abstract class AbstractProductCall extends AbstractApiCall
{
    /**
     * @var string
     */
    protected $productSlug;

    /**
     * @var int
     */
    protected $productId;

    /**
     * Sets product ID, resets product slug.
     *
     * @param int $id
     *
     * @return self
     *
     * @throws \InvalidArgumentException when non numeric value provided
     */
    public function setProductId($id)
    {
        if (!is_numeric($id)) {
            throw new \InvalidArgumentException('Non numeric value provided');
        }

        $this->productId = (int) $id;
        $this->productSlug = null;

        return $this;
    }

    /**
     * Sets product slug, resets product ID.
     *
     * @param string $slug
     *
     * @return self
     *
     * @throws \InvalidArgumentException when empty slug provided
     */
    public function setProductSlug($slug)
    {
        if (empty($slug)) {
            throw new \InvalidArgumentException('Empty slug provided');
        }

        $this->productSlug = (string) $slug;
        $this->productId = null;

        return $this;
    }

    /**
     * @param string $suffix
     *
     * @return string
     *
     * @throws \RuntimeException when product ID and slug not set or both set
     */
    protected function buildPath($suffix = '')
    {
        if ($this->productId && $this->productSlug) {
            throw new \RuntimeException('Both product ID and product slug set');
        } elseif ($this->productId) {
            return sprintf(
                '/api/products/%d%s',
                $this->productId,
                $suffix
            );
        } elseif ($this->productSlug) {
            return sprintf(
                '/api/products/%s%s',
                $this->productSlug,
                $suffix
            );
        } else {
            throw new \RuntimeException('Product ID or product slug is not set');
        }
    }
}
