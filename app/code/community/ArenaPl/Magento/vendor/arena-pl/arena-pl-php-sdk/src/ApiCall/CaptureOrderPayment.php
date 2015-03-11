<?php

namespace ArenaPl\ApiCall;

use ArenaPl\ApiCall\Traits\OrderPaymentTrait;
use ArenaPl\Exception\ApiCallException;

class CaptureOrderPayment extends AbstractOrderCall implements ApiCallInterface
{
    use OrderPaymentTrait;

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
     * @throws \RuntimeException when order payment ID is not set
     */
    public function getPath()
    {
        if (!$this->paymentId) {
            throw new \RuntimeException('Payment ID not set');
        }

        return $this->buildPath(sprintf(
            '/payments/%d/capture',
            $this->paymentId
        ));
    }

    /**
     * Returns order payment data.
     *
     * Use with COD shipments only.
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
