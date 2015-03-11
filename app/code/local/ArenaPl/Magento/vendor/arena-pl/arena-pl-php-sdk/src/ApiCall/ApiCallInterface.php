<?php

namespace ArenaPl\ApiCall;

use ArenaPl\Exception\ApiCallException;
use GuzzleHttp\Message\ResponseInterface;

/**
 * Every API call object implements this interface.
 */
interface ApiCallInterface
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const SEARCH_METHOD_CONTAINS = 'cont';
    const SEARCH_METHOD_NOT_CONTAINS = 'not_cont';
    const SEARCH_METHOD_EQUALS = 'eq';
    const SEARCH_METHOD_NOT_EQUALS = 'not_eq';
    const SEARCH_METHOD_STARTS_WITH = 'start';
    const SEARCH_METHOD_NOT_STARTS_WITH = 'not_start';
    const SEARCH_METHOD_ENDS_WITH = 'end';
    const SEARCH_METHOD_NOT_ENDS_WITH = 'not_end';
    const SEARCH_METHOD_GREATER_THAN = 'gt';
    const SEARCH_METHOD_LESS_THAN = 'lt';
    const SORT_ASC = 'asc';
    const SORT_DESC = 'desc';

    /**
     * Returns one of HTTP method.
     *
     * @return string
     */
    public function getMethod();

    /**
     * Returns URL path.
     *
     * @return string
     */
    public function getPath();

    /**
     * Returns query items to send to API.
     *
     * @return array
     */
    public function getQuery();

    /**
     * Returns body items to send to API.
     *
     * @return array
     */
    public function getBody();

    /**
     * Executes API call.
     *
     * @return mixed
     *
     * @throws ApiCallException
     */
    public function getResult();

    /**
     * Metadata fields to load after API call.
     *
     * @return string[]
     */
    public function getMetadataFields();

    /**
     * Returns response if in need to inspect.
     *
     * @return ResponseInterface|null
     */
    public function getRawResponse();
}
