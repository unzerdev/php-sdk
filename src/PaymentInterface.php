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

interface PaymentInterface extends AmountsInterface
{
    //<editor-fold desc="Transactions">
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

    /**
     * Cancel all charges in the payment.
     */
    public function cancelAllCharges();
    //</editor-fold>
}
