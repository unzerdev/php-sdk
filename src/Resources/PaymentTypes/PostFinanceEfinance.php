<?php

/**
 * This represents the Post Finance Card payment type.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Traits\CanDirectCharge;

class PostFinanceEfinance extends BasePaymentType
{
    use CanDirectCharge;
}
