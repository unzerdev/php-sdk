<?php

namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Adapter\HttpAdapterInterface;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Traits\CanAuthorize;
use UnzerSDK\Traits\CanDirectCharge;

class ClickToPay extends BasePaymentType
{

    use CanDirectCharge;
    use CanAuthorize;

    protected $mcCorrelationId;
    protected $mcCxFlowId;
    protected $mcMerchantTransactionId;
    protected $brand;


    public function __construct(
        ?string $mcCorrelationId = null,
        ?string $mcCxFlowId = null,
        ?string $mcMerchantTransactionId = null,
        ?string $brand = null
    )
    {
        $this->mcCorrelationId = $mcCorrelationId;
        $this->mcCxFlowId = $mcCxFlowId;
        $this->mcMerchantTransactionId = $mcMerchantTransactionId;
        $this->brand = $brand;
    }

    public static function getResourceName(): string
    {
        return 'clicktopay';
    }


    public function getMcCorrelationId()
    {
        return $this->mcCorrelationId;
    }


    public function setMcCorrelationId($mcCorrelationId): ClickToPay
    {
        $this->mcCorrelationId = $mcCorrelationId;
        return $this;
    }


    public function getMcCxFlowId(): ClickToPay
    {
        return $this->mcCxFlowId;
    }


    public function setMcCxFlowId($mcCxFlowId): ClickToPay
    {
        $this->mcCxFlowId = $mcCxFlowId;
        return $this;
    }


    public function getMcMerchantTransactionId()
    {
        return $this->mcMerchantTransactionId;
    }


    public function setMcMerchantTransactionId($mcMerchantTransactionId): ClickToPay
    {
        $this->mcMerchantTransactionId = $mcMerchantTransactionId;
        return $this;
    }


    public function getBrand()
    {
        return $this->brand;
    }


    public function setBrand($brand)
    {
        $this->brand = $brand;
        return $this;
    }


}