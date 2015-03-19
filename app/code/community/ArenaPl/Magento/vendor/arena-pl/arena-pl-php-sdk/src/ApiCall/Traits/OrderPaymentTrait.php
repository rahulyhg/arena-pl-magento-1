<?php

namespace ArenaPl\ApiCall\Traits;

trait OrderPaymentTrait
{
    /**
     * @var int
     */
    protected $paymentId;

    /**
     * @param int $paymentId
     *
     * @return self
     *
     * @throws \InvalidArgumentException when non numeric value provided
     */
    public function setPaymentId($paymentId)
    {
        if (!is_numeric($paymentId)) {
            throw new \InvalidArgumentException('Non numeric value provided');
        }

        $this->paymentId = (int) $paymentId;

        return $this;
    }
}
