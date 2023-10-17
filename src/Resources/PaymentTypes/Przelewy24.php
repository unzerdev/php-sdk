<?php
/**
 * This represents the Przelewy24 payment type.
 *
 * @link  https://docs.unzer.com/
 *
 * @package  UnzerSDK\PaymentTypes
 */

namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Traits\CanDirectCharge;

class Przelewy24 extends BasePaymentType
{
    use CanDirectCharge;
}
