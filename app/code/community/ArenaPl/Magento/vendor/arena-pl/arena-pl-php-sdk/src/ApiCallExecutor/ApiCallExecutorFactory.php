<?php

namespace ArenaPl\ApiCallExecutor;

class ApiCallExecutorFactory
{
    /**
     * Sprintf format to build endpoint URL.
     */
    const ENDPOINT_URL = 'http%s://%s.arena.pl/api';

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @var string
     */
    protected $token;

    /**
     * @param string $subdomain
     * @param string $token
     * @param bool   $forceHttp
     *
     * @return self
     */
    public function __construct($subdomain, $token, $forceHttp)
    {
        $this->endpoint = $this->buildEndpointUrl($subdomain, (bool) $forceHttp);
        $this->token = (string) $token;

        return $this;
    }

    /**
     * @return ApiCallExecutorInterface
     */
    public function getApiCallExecutor()
    {
        return new GuzzleExecutor($this->endpoint, $this->token);
    }

    /**
     * @return string
     */
    public function getEndpointUrl()
    {
        return $this->endpoint;
    }

    /**
     * @param string $subdomain
     * @param bool   $forceHttp
     *
     * @return string
     */
    protected function buildEndpointUrl($subdomain, $forceHttp)
    {
        return sprintf(
            self::ENDPOINT_URL,
            $forceHttp ? '' : 's',
            $subdomain
        );
    }
}
