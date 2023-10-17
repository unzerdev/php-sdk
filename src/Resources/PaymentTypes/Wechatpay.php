<?php
/**
 * This represents the Wechatpay payment type.
 *
 * @link  https://docs.unzer.com/
 *
 * @package  UnzerSDK\PaymentTypes
 */

namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Traits\CanDirectCharge;

class Wechatpay extends BasePaymentType
{
    use CanDirectCharge;
}
