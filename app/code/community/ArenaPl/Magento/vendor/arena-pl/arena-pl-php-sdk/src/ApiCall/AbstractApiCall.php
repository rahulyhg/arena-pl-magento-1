<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCallExecutor\ApiCallExecutorInterface;
use ArenaPl\Exception\ApiCallException;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Stream\StreamInterface;

abstract class AbstractApiCall
{
    /**
     * @var string|null
     */
    protected static $metadataPackToLoad = null;

    /**
     * @var array
     */
    protected $query = [];

    /**
     * @var array
     */
    protected $body = [];

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var ApiCallExecutorInterface
     */
    protected $apiCallExecutor;

    /**
     * @var SearchHelper|null
     */
    protected $searchHelper;

    /**
     * @var MetadataHelper
     */
    protected $metadataHelper;

    /**
     * @param ApiCallExecutorInterface $apiCallExecutor
     *
     * @return self
     */
    public function __construct(ApiCallExecutorInterface $apiCallExecutor)
    {
        $this->apiCallExecutor = $apiCallExecutor;
        $this->metadataHelper = $this->initMetadataHelper();

        return $this;
    }

    /**
     * @return MetadataHelper
     */
    protected function initMetadataHelper()
    {
        return new MetadataHelper($this);
    }

    /**
     * @param int $page
     *
     * @return self
     *
     * @throws \InvalidArgumentException when non numeric value provided
     */
    protected function setPage($page)
    {
        if (!is_numeric($page)) {
            throw new \InvalidArgumentException('Non numeric value provided');
        }

        $this->query['page'] = (int) $page;

        return $this;
    }

    /**
     * @param int $resultsPerPage
     *
     * @return self
     *
     * @throws \InvalidArgumentException when non numeric value provided
     */
    protected function setResultsPerPage($resultsPerPage)
    {
        if (!is_numeric($resultsPerPage)) {
            throw new \InvalidArgumentException('Non numeric value provided');
        }

        $this->query['per_page'] = (int) $resultsPerPage;

        return $this;
    }

    /**
     * @param string $field
     * @param string $order
     *
     * @return self
     *
     * @throws \InvalidArgumentException when order is not valid
     */
    protected function setSort($field, $order)
    {
        static $validOrder = [
            ApiCallInterface::SORT_ASC,
            ApiCallInterface::SORT_DESC,
        ];

        if (!in_array($order, $validOrder)) {
            throw new \InvalidArgumentException(sprintf(
                'Sort order "%s" is not valid, use one of "%s"',
                $order,
                implode(', ', $validOrder)
            ));
        }

        $this->query['q[s]'] = sprintf('%s %s', $field, $order);

        return $this;
    }

    /**
     * @param string $field
     * @param scalar $value
     * @param string $method
     *
     * @return self
     *
     * @throws \InvalidArgumentException when search method is not valid
     */
    protected function setSearch($field, $value, $method)
    {
        $this->searchHelper = new SearchHelper($this);
        $this->searchHelper
            ->setField($field)
            ->setMethod($method)
            ->setValue($value);

        return $this;
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        $dataToMerge = [];

        if ($this->searchHelper instanceof SearchHelper) {
            $dataToMerge[$this->searchHelper->getKey()] = $this->searchHelper->getValue();
        }

        return array_merge($this->query, $dataToMerge);
    }

    /**
     * @return array
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Makes API call and tries to decode JSON response to associative array.
     *
     * @param string|null $returnResponseKey response key to return
     *
     * @return array
     *
     * @throws ApiCallException when unable to decode JSON response or response key not present
     */
    protected function makeCallJSON($returnResponseKey = null)
    {
        $this->makeRawApiCall();

        $decoded = $this->decodeJson($this->response);

        $currentMetadataFields = $this->getMetadataFields();
        if (!empty($currentMetadataFields)) {
            $this->metadataHelper->setMetadata($decoded);
        }

        // API call is expecting only certain response key
        if ($returnResponseKey) {
            if (!array_key_exists($returnResponseKey, $decoded)) {
                throw new ApiCallException(null, $this->response, sprintf(
                    'Malformed response, "%s" not present',
                    $returnResponseKey
                ));
            }

            return $decoded[$returnResponseKey];
        }

        return $decoded;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return array
     *
     * @throws ApiCallException when unable to decode JSON response
     */
    protected function decodeJson(ResponseInterface $response)
    {
        $responseBody = $response->getBody();
        if (!$responseBody instanceof StreamInterface) {
            throw new ApiCallException(null, $response, 'Unable to decode response (no body)');
        }

        $isSeekable = $responseBody->isSeekable();
        if ($isSeekable && $responseBody->tell() !== 0) {
            $responseBody->seek(0);
        }

        $decodedJson = json_decode($responseBody->getContents(), true);

        if ($isSeekable) {
            $responseBody->seek(0);
        }

        if (!is_array($decodedJson)) {
            throw new ApiCallException(null, $response, 'Unable to decode response');
        }

        return $decodedJson;
    }

    /**
     * Makes API call and checks if response status code is the same as expected.
     *
     * @param int $expectedStatusCode
     *
     * @return bool
     */
    protected function makeCall($expectedStatusCode)
    {
        $this->makeRawApiCall();

        return $expectedStatusCode === $this->response->getStatusCode();
    }

    protected function makeRawApiCall()
    {
        $this->response = $this->apiCallExecutor->makeAPICall($this);
    }

    /**
     * Metadata fields to load after API call.
     *
     * @return string[]
     */
    public function getMetadataFields()
    {
        return static::$metadataPackToLoad
            ? MetadataHelper::getMetadataPack(static::$metadataPackToLoad)
            : [];
    }

    /**
     * @return ResponseInterface|null
     */
    public function getRawResponse()
    {
        return $this->response;
    }
}
