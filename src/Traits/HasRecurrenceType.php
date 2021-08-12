<?php
/**
 * This trait adds the short id and unique id property to a class.
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
 * @author  Simon Gabriel <development@unzer.com>
 *
 * @package  UnzerSDK\Traits
 */
namespace UnzerSDK\Traits;

use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;

trait HasRecurrenceType
{
    //<editor-fold desc="Getters/Setters">

    /**
     * @param BasePaymentType|null $paymentType
     *
     * @return string|null
     */
    public function getRecurrenceType(BasePaymentType $paymentType = null): ?string
    {
        if ($paymentType === null && $this instanceof AbstractTransactionType) {
            $payment = $this->getPayment();
            if ($payment === null || $payment->getPaymentType() === null) {
                return null;
            }
            $paymentType = $payment->getPaymentType();
        }

        $additionalTransactionData = $this->getAdditionalTransactionData();
        $method = $paymentType::getResourceName();
        return $additionalTransactionData->$method->recurrenceType ?? null;
    }

    /**
     * @param string               $recurrenceType
     * @param BasePaymentType|null $paymentType
     *
     * @return $this
     */
    public function setRecurrenceType(string $recurrenceType, BasePaymentType $paymentType = null): self
    {
        if ($paymentType === null) {
            $payment = $this->getPayment();
            if ($payment === null || $payment->getPaymentType() === null) {
                throw new \RuntimeException('Payment Type has to be set before setting the recurrenceType');
            }
            $paymentType = $payment->getPaymentType();
        }
        $this->addAdditionalTransactionData($paymentType::getResourceName(), (object)['recurrenceType' => $recurrenceType]);
        return $this;
    }

    //</editor-fold>
}
