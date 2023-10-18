<?php
/**
 * This represents the paylater invoice payment type.
 *
 * @link  https://docs.unzer.com/
 *
 */

namespace UnzerSDK\Resources\PaymentTypes;

class PaylaterInvoice extends BasePaymentType
{
    protected const SUPPORT_DIRECT_PAYMENT_CANCEL = true;
}
