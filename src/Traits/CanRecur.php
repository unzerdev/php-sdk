<?php
/**
 * This trait allows a payment type to activate recurring payments.
 *
 * Copyright (C) 2019 heidelpay GmbH
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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\Traits
 */
namespace heidelpayPHP\Traits;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\AbstractHeidelpayResource;
use heidelpayPHP\Resources\Recurring;
use RuntimeException;

trait CanRecur
{
    /** @var bool $recurring */
    private $recurring = false;

    /**
     * Activates recurring payment for the payment type.
     *
     * @param string $returnUrl The URL to which the customer gets redirected in case of a 3ds transaction
     *
     * @return Recurring
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function activateRecurring($returnUrl): Recurring
    {
        if ($this instanceof AbstractHeidelpayResource) {
            return $this->getHeidelpayObject()->activateRecurringPayment($this, $returnUrl);
        }
        throw new RuntimeException('Error: Recurring can not be enabled on this type.');
    }

    //<editor-fold desc="Getter/Setter">

    /**
     * @return bool
     */
    public function isRecurring(): bool
    {
        return $this->recurring;
    }

    /**
     * @param bool $active
     *
     * @return self
     */
    protected function setRecurring(bool $active): self
    {
        $this->recurring = $active;
        return $this;
    }

    //</editor-fold>
}
