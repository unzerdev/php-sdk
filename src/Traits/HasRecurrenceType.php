<?php

/**
 * This trait adds the short id and unique id property to a class.
 *
 * Copyright (C) 2021 - today Unzer E-Com GmbH
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

use RuntimeException;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;
use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;

trait HasRecurrenceType
{
    //<editor-fold desc="Getters/Setters">

    /**
     * @return string|null
     */
    public function getRecurrenceType(): ?string
    {
        $additionalTransactionData = $this->getAdditionalTransactionData();
        if ($additionalTransactionData !== null) {
            foreach ($additionalTransactionData as $data) {
                if (property_exists($data, 'recurrenceType')) {
                    return $data->recurrenceType ?? null;
                }
            }
        }

        return null;
    }

    /**
     * @param string               $recurrenceType Recurrence type used for recurring payment.
     * @param BasePaymentType|null $paymentType    If provided recurrenceType is set based on this payment type.
     *                                             This is required for recurring transaction, since the type can not be
     *                                             determined automatically.
     *
     * @return $this
     */
    public function setRecurrenceType(string $recurrenceType, BasePaymentType $paymentType = null): self
    {
        if ($paymentType === null && $this instanceof AbstractTransactionType) {
            $payment = $this->getPayment();
            $paymentType = $payment ? $payment->getPaymentType() : null;
        }

        if ($paymentType === null) {
            throw new RuntimeException('Payment type can not be determined. Set it first or provide it via parameter $paymentType.');
        }
        $recurrenceTypeObject = (object)['recurrenceType' => $recurrenceType];
        $this->addAdditionalTransactionData($paymentType::getResourceName(), $recurrenceTypeObject);

        return $this;
    }

    //</editor-fold>
}
