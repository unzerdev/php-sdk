<?php

namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Traits\HasAccountInformation;
use UnzerSDK\Traits\CanDirectCharge;

class Twint extends BasePaymentType
{
    use HasAccountInformation;
    use CanDirectCharge;
}
