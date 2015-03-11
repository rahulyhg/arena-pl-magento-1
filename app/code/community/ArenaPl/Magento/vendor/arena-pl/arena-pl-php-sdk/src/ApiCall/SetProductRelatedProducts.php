<?php

namespace ArenaPl\ApiCall;

class SetProductRelatedProducts extends AbstractProductCall implements ApiCallInterface
{
    /**
     * @var array
     */
    protected $relatedProductIds = [];

    /**
     * @param array $relatedProductIds
     *
     * @return self
     *
     * @throws \InvalidArgumentException when one or more values are not numeric
     */
    public function setRelatedProductIds(array $relatedProductIds)
    {
        $numericValues = array_filter($relatedProductIds, 'is_numeric');
        if (count($numericValues) != count($relatedProductIds)) {
            throw new \InvalidArgumentException('One or more values are not numeric');
        }

        $this->relatedProductIds = array_map('intval', $relatedProductIds);

        return $this;
    }

    /**
     * @param int $relatedProductId
     *
     * @return self
     *
     * @throws \InvalidArgumentException when non numeric value provided
     */
    public function addRelatedProductId($relatedProductId)
    {
        if (!is_numeric($relatedProductId)) {
            throw new \InvalidArgumentException('Non numeric value provided');
        }

        $this->relatedProductIds[] = (int) $relatedProductId;

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
     */
    public function getPath()
    {
        return $this->buildPath('/relate');
    }

    /**
     * Returns true if relation correctly set.
     *
     * @return bool
     */
    public function getResult()
    {
        $this->processRelatedProducts();

        return $this->makeCall(200);
    }

    protected function processRelatedProducts()
    {
        $this->query = [
            'ids' => implode(',', array_unique($this->relatedProductIds)),
        ];
    }
}
