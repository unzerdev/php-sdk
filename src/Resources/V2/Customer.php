<?php

namespace UnzerSDK\Resources\V2;

use UnzerSDK\Apis\PaymentApiConfigBearerAuth;
use UnzerSDK\Constants\ApiVersions;
use UnzerSDK\Resources\Customer as CustomerV1;

class Customer extends CustomerV1
{
    public function getApiVersion(): string
    {
        return ApiVersions::V2;
    }

    public function getApiConfig(): string
    {
        return PaymentApiConfigBearerAuth::class;
    }


}