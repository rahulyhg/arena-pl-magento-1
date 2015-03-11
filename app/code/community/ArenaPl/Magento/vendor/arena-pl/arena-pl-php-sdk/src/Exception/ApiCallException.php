<?php

namespace ArenaPl\Exception;

use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;

class ApiCallException extends \RuntimeException
{
    /**
     * @var RequestInterface|null
     */
    protected $request;

    /**
     * @var ResponseInterface|null
     */
    protected $response;

    /**
     * @param RequestInterface|null  $request
     * @param ResponseInterface|null $response
     * @param string                 $message
     */
    public function __construct(
        RequestInterface $request = null,
        ResponseInterface $response = null,
        $message = ''
    ) {
        $this->request = $request;
        $this->response = $response;

        parent::__construct($message);
    }

    /**
     * @return RequestInterface|null
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}
