<?php
/**
 * This represents the Klarna payment type.
 *
 * @link  https://docs.unzer.com/
 *
 * @package  UnzerSDK\PaymentTypes
 */

namespace UnzerSDK\Resources\PaymentTypes;

class Klarna extends BasePaymentType
{
    protected const SUPPORT_DIRECT_PAYMENT_CANCEL = true;
}
