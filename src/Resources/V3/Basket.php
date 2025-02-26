<?php

namespace UnzerSDK\Resources\V3;

use UnzerSDK\Apis\PaymentApiConfigBearerAuth;
use UnzerSDK\Constants\ApiVersions;
use UnzerSDK\Resources\Basket as BasketV2;

class Basket extends BasketV2
{
    public function __construct(
        string $orderId = '',
        float  $totalValueGross = 0.0,
        string $currencyCode = 'EUR',
        array  $basketItems = []
    )
    {
        $this->orderId = $orderId;
        $this->setTotalValueGross($totalValueGross);
        $this->currencyCode = $currencyCode;
        $this->setBasketItems($basketItems);
    }

    public function getApiVersion(): string
    {
        return ApiVersions::V3;
    }

    public function getApiConfig(): string
    {
        return PaymentApiConfigBearerAuth::class;
    }
}
