<?php
/**
 * This represents the Alipay payment type.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Traits\CanDirectCharge;

class Alipay extends BasePaymentType
{
    use CanDirectCharge;
}
