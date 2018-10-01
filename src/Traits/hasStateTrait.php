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

namespace heidelpay\NmgPhpSdk\Traits;

use heidelpay\NmgPhpSdk\Constants\PaymentState;

trait HasStateTrait
{
    /** @var int */
    private $state = 0;

    //<editor-fold desc="Check for States">
    /**
     * Return true if the state is pending.
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->getState() === PaymentState::STATE_PENDING;
    }

    /**
     * Return true if the state is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->getState() === PaymentState::STATE_COMPLETED;
    }

    /**
     * Return true if the state is canceled.
     *
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->getState() === PaymentState::STATE_CANCELED;
    }

    /**
     * Return true if the state is partly paid.
     *
     * @return bool
     */
    public function isPartlyPaid(): bool
    {
        return $this->getState() === PaymentState::STATE_PARTLY;
    }

    /**
     * Return true if the state is payment review.
     *
     * @return bool
     */
    public function isPaymentReview(): bool
    {
        return $this->getState() === PaymentState::STATE_PAYMENT_REVIEW;
    }

    /**
     * Return true if the state is chargeback.
     *
     * @return bool
     */
    public function isChargeBack(): bool
    {
        return $this->getState() === PaymentState::STATE_CHARGEBACK;
    }
    //</editor-fold>

    //<editor-fold desc="Getters/Setters">
    /**
     * Returns the current state code (ref. Constants/PaymentState).
     *
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }

    /**
     * Sets the current state.
     *
     * @param int $state
     * @return self
     */
    public function setState(int $state): self
    {
        $this->state = $state;
        return $this;
    }
    //</editor-fold>
}
