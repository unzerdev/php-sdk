<?php
/**
 * This interface defines the methods for the Payment class.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/interfaces
 */
namespace heidelpay\MgwPhpSdk\Interfaces;

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
