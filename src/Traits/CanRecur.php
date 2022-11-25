<?php
/**
 * This trait allows a payment type to activate recurring payments.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
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
 * @link  https://docs.unzer.com/
 *
 * @package  UnzerSDK\Traits
 */
namespace UnzerSDK\Traits;

use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\AbstractUnzerResource;
use UnzerSDK\Resources\Recurring;
use RuntimeException;

trait CanRecur
{
    /** @var bool $recurring */
    private $recurring = false;

    /**
     * Activates recurring payment for the payment type.
     *
     * @param string     $returnUrl      The URL to which the customer gets redirected in case of a 3ds transaction
     * @param null|mixed $recurrenceType Recurrence type used for recurring payment.
     *
     * @return Recurring
     *
     * @throws UnzerApiException An UnzerApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException  A RuntimeException is thrown when there is an error while using the SDK.
     *
     * @deprecated since 1.3.0.0 Please set the recurrence type in your Charge/Authorize object using `setRecurrenceType`
     *             before performing the transaction request.
     *
     */
    public function activateRecurring($returnUrl, $recurrenceType = null): Recurring
    {
        if ($this instanceof AbstractUnzerResource) {
            return $this->getUnzerObject()->activateRecurringPayment($this, $returnUrl, $recurrenceType);
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
