<?php
/**
 * This represents the GiroPay payment type.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Traits\CanDirectCharge;

class Giropay extends BasePaymentType
{
    use CanDirectCharge;
}
