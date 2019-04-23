<?php
/**
 * This trait makes a payment type authorizable with mandatory customer.
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

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Interfaces\HeidelpayParentInterface;
use heidelpayPHP\Resources\Basket;
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use RuntimeException;

trait CanAuthorizeWithCustomer
{
    /**
     * Authorize an amount with the given currency.
     * Throws HeidelpayApiException if the transaction could not be performed (e. g. increased risk etc.).
     *
     * @param $amount
     * @param $currency
     * @param $returnUrl
     * @param Customer|string $customer
     * @param string|null     $orderId
     * @param Metadata|null   $metadata
     * @param Basket|null     $basket   The Basket object corresponding to the payment.
     *                                  The Basket object will be created automatically if it does not exist
     *                                  yet (i.e. has no id).
     *
     * @return Authorization
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function authorize(
        $amount,
        $currency,
        $returnUrl,
        $customer,
        $orderId = null,
        $metadata = null,
        $basket = null
    ): Authorization {
        if ($this instanceof HeidelpayParentInterface) {
            return $this->getHeidelpayObject()->authorize(
                $amount,
                $currency,
                $this,
                $returnUrl,
                $customer,
                $orderId,
                $metadata,
                $basket
            );
        }

        throw new RuntimeException(
            self::class . ' must implement HeidelpayParentInterface to enable ' . __METHOD__ . ' transaction.'
        );
    }
}
