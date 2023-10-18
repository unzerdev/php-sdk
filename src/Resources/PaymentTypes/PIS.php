<?php
/**
 * This represents the Sofort payment type.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Traits\CanDirectCharge;

class PIS extends BasePaymentType
{
    use CanDirectCharge;
}
