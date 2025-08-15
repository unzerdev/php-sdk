<?php

namespace UnzerSDK\Resources\EmbeddedResources;

use UnzerSDK\Resources\AbstractUnzerResource;

/**
 * Represents the `eventDependentPayment` object for Wero additional transaction data.
 *
 * Allowed values
 * - captureTrigger: SHIPPING, DELIVERY, AVAILABILITY, SERVICEFULFILMENT, OTHER
 * - amountPaymentType: PAY, PAYUPTO
 */
class WeroEventDependentPayment extends AbstractUnzerResource
{
    protected ?string $captureTrigger = null;
    protected ?string $amountPaymentType = null;
    protected ?int $maxAuthToCaptureTime = null;
    protected ?bool $multiCapturesAllowed = null;

    public function getCaptureTrigger(): ?string
    {
        return $this->captureTrigger;
    }

    public function setCaptureTrigger(?string $captureTrigger): WeroEventDependentPayment
    {
        $this->captureTrigger = $captureTrigger;
        return $this;
    }

    public function getAmountPaymentType(): ?string
    {
        return $this->amountPaymentType;
    }

    public function setAmountPaymentType(?string $amountPaymentType): WeroEventDependentPayment
    {
        $this->amountPaymentType = $amountPaymentType;
        return $this;
    }

    /**
     * @return string|int|null
     */
    public function getMaxAuthToCaptureTime()
    {
        return $this->maxAuthToCaptureTime;
    }

    /**
     * @param string|int|null $maxAuthToCaptureTime
     */
    public function setMaxAuthToCaptureTime($maxAuthToCaptureTime): WeroEventDependentPayment
    {
        $this->maxAuthToCaptureTime = $maxAuthToCaptureTime;
        return $this;
    }

    /**
     * @return bool|string|null
     */
    public function getMultiCapturesAllowed()
    {
        return $this->multiCapturesAllowed;
    }

    /**
     * @param bool|string|null $multiCapturesAllowed
     */
    public function setMultiCapturesAllowed($multiCapturesAllowed): WeroEventDependentPayment
    {
        $this->multiCapturesAllowed = $multiCapturesAllowed;
        return $this;
    }
}
