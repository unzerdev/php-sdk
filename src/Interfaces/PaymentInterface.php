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
namespace heidelpay\NmgPhpSdk\Interfaces;

use heidelpay\NmgPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\NmgPhpSdk\Resources\TransactionTypes\Charge;

interface PaymentInterface extends AmountsInterface
{
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
