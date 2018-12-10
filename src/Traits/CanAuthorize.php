<?php
/**
 * This trait makes a payment type authorizable.
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
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\TransactionTypes\Authorization;

trait CanAuthorize
{
    /**
     * Authorize an amount with the given currency.
     * Throws HeidelpayApiException if the transaction could not be performed (e. g. increased risk etc.).
     *
     * @param float                $amount
     * @param string               $currency
     * @param string               $returnUrl
     * @param Customer|string|null $customer
     * @param string|null          $orderId
     * @param Metadata|string|null $metadata
     *
     * @return Authorization
     *
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function authorize(
        $amount,
        $currency,
        $returnUrl,
        $customer = null,
        $orderId = null,
        $metadata = null
    ): Authorization {
        if ($this instanceof HeidelpayParentInterface) {
            return $this->getHeidelpayObject()->authorize(
                $amount,
                $currency,
                $this,
                $returnUrl,
                $customer,
                $orderId,
                $metadata
            );
        }

        throw new \RuntimeException(
            self::class . ' must implement HeidelpayParentInterface to enable ' . __METHOD__ . ' transaction.'
        );
    }
}
