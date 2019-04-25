<?php
/**
 * This trait adds the state property to a class.
 *
 * Copyright (C) 2018 heidelpay GmbH
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/traits
 */
namespace heidelpayPHP\Traits;

use heidelpayPHP\Constants\PaymentState;
use RuntimeException;

trait HasPaymentState
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
     * Returns the current state code (ref. Constants/PaymentState).
     *
     * @return string The name of the current payment state.
     *
     * @throws RuntimeException
     */
    public function getStateName(): string
    {
        return PaymentState::mapStateCodeToName($this->state);
    }

    /**
     * Sets the current state.
     *
     * @param int $state
     *
     * @return self
     */
    public function setState(int $state): self
    {
        $this->state = $state;
        return $this;
    }

    //</editor-fold>
}
