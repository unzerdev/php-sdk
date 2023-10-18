<?php
/**
 * This represents the prepayment payment type.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Traits\CanDirectCharge;

class Prepayment extends BasePaymentType
{
    use CanDirectCharge;
}
