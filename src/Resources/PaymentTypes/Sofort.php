<?php

namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Traits\CanDirectCharge;

class Sofort extends BasePaymentType
{
    use CanDirectCharge;
}
