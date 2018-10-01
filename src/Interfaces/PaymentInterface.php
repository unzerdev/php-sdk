<?php
/**
 * Description
 *
 * LICENSE
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
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
