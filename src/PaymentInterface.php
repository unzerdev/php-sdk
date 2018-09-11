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

use heidelpay\NmgPhpSdk\PaymentTypes\PaymentTypeInterface;
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
    public function charge($amount = null, $currency = null, $returnUrl = null): Charge;

    /**
     * Performs a full charge on the payment.
     * Works only if an authorization has been performed prior to this call.
     *
     * @return Charge
     */
    public function fullCharge(): Charge;

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

    //<editor-fold desc="Payment state">
    /**
     * Return true if the state is pending.
     *
     * @return bool
     */
    public function isPending(): bool;

    /**
     * Return true if the state is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool;

    /**
     * Return true if the state is canceled.
     *
     * @return bool
     */
    public function isCanceled(): bool;

    /**
     * Return true if the state is partly paid.
     *
     * @return bool
     */
    public function isPartlyPaid(): bool;

    /**
     * Return true if the state is payment review.
     *
     * @return bool
     */
    public function isPaymentReview(): bool;

    /**
     * Return true if the state is chargeback.
     *
     * @return bool
     */
    public function isChargeBack(): bool;

    /**
     * Returns the current state code (ref. Constants/PaymentState).
     *
     * @return int
     */
    public function getState(): int;
    //</editor-fold>

    /**
     * Returns the paymentType and throws a MissingResourceException if it is not set.
     *
     * @return PaymentTypeInterface
     */
    public function getPaymentType(): PaymentTypeInterface;
}
