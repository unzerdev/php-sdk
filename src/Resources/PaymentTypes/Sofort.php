<?php
/**
 * This represents the Sofort payment type.
 *
 * @link  https://docs.unzer.com/
 *
 * @package  UnzerSDK\PaymentTypes
 */

namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Traits\CanDirectCharge;

class Sofort extends BasePaymentType
{
    use CanDirectCharge;
}
