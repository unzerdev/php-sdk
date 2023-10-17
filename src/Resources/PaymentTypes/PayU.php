<?php

/**
 * This represents the PayU payment type.
 *
 * @link  https://docs.unzer.com/
 *
 * @package  UnzerSDK\PaymentTypes
 */

namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Traits\CanDirectCharge;

class PayU extends BasePaymentType
{
    use CanDirectCharge;
}
