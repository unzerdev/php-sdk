<?php
/**
 * Adds payout capability to payment types.
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
use UnzerSDK\Interfaces\UnzerParentInterface;
use UnzerSDK\Resources\Basket;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\TransactionTypes\Payout;
use RuntimeException;

trait CanPayout
{
    /**
     * Credit the given amount with the given currency to this payment type.
     * Throws UnzerApiException if the transaction could not be performed (e. g. increased risk etc.).
     *
     * @param float                $amount
     * @param string               $currency
     * @param string               $returnUrl
     * @param Customer|string|null $customer
     * @param string|null          $orderId
     * @param Metadata|string|null $metadata
     * @param Basket|null          $basket           The Basket object corresponding to the payment.
     *                                               The Basket object will be created automatically if it does not exist
     *                                               yet (i.e. has no id).
     * @param string|null          $invoiceId        The external id of the invoice.
     * @param string|null          $paymentReference A reference text for the payment.
     *
     * @return Payout The resulting payout object.
     *
     * @throws UnzerApiException An UnzerApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException  A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function payout(
        $amount,
        $currency,
        $returnUrl,
        $customer = null,
        $orderId = null,
        $metadata = null,
        $basket = null,
        $invoiceId = null,
        $paymentReference = null
    ): Payout {
        if ($this instanceof UnzerParentInterface) {
            return $this->getUnzerObject()->payout(
                $amount,
                $currency,
                $this,
                $returnUrl,
                $customer,
                $orderId,
                $metadata,
                $basket,
                $invoiceId,
                $paymentReference
            );
        }

        throw new RuntimeException(
            self::class . ' must implement UnzerParentInterface to enable ' . __METHOD__ . ' transaction.'
        );
    }
}
