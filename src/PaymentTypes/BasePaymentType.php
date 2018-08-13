<?php
/**
 * Description
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/${Package}
 */

namespace heidelpay\NmgPhpSdk\PaymentTypes;

use heidelpay\NmgPhpSdk\AbstractHeidelpayResource;
use heidelpay\NmgPhpSdk\Exceptions\IllegalTransactionTypeException;
use heidelpay\NmgPhpSdk\TransactionTypes\Authorization;
use heidelpay\NmgPhpSdk\TransactionTypes\Charge;

class BasePaymentType extends AbstractHeidelpayResource implements PaymentTypeInterface
{
    public function charge($amount, $currency): Charge
    {
        throw new IllegalTransactionTypeException('charge');
    }

    public function authorize($amount, $currency): Authorization
    {
        throw new IllegalTransactionTypeException('authorize');
    }

    public function cancel($amount = 0.0)
    {
        throw new IllegalTransactionTypeException('cancel');
    }
}
