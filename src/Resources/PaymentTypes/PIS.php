<?php

namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Traits\CanDirectCharge;

/** @deprecated PIS (Unzer Bank Transfer) payment type is no longer supported and will be removed in a future version. */
class PIS extends BasePaymentType
{
    use CanDirectCharge;
}
