<?php

namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Traits\CanDirectCharge;
use UnzerSDK\Traits\HasAccountInformation;

/** @deprecated Sofort payment type is no longer supported and will be removed in a future version. */
class Sofort extends BasePaymentType
{
    use HasAccountInformation;
    use CanDirectCharge;
}
