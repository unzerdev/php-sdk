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
namespace heidelpay\NmgPhpSdk;

use heidelpay\NmgPhpSdk\TransactionTypes\Authorization;
use heidelpay\NmgPhpSdk\TransactionTypes\Charge;

interface PaymentInterface
{
    const STATE_PENDING = '0';
    const STATE_COMPLETED = '1';
    const STATE_CANCELED = '2';
    const STATE_PARTLY = '3';
    const STATE_PAYMENT_REVIEW = '4';
    const STATE_CHARGEBACK = '5';

    /**
     * @param float $amount
     * @param string $currency
     * @param string $returnUrl
     * @return Charge
     */
    public function charge($amount, $currency, $returnUrl): Charge;

    /**
     * @param float $amount
     * @param string $currency
     * @param string $returnUrl
     * @return Authorization
     */
    public function authorize($amount, $currency, $returnUrl): Authorization;

    /**
     * @param float $amount
     */
    public function cancel($amount = null);
}
