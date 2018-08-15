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

use heidelpay\NmgPhpSdk\TransactionTypes\Authorization;
use heidelpay\NmgPhpSdk\TransactionTypes\Charge;

interface PaymentTypeInterface
{
    /**
     * @param float $amount
     * @param string $currency
     * @return Charge
     */
    public function charge($amount = null, $currency = ''): Charge;

    /**
     * @param float $amount
     * @param string $currency
     * @param $returnUrl
     * @return Authorization
     */
    public function authorize($amount, $currency, $returnUrl): Authorization;

    /**
     * @param float $amount
     */
    public function cancel($amount = 0.0);
}
