<?php
/**
 * This trait makes a payment type chargeable with mandatory customer.
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
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/traits
 */
namespace heidelpay\MgwPhpSdk\Traits;

use heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException;
use heidelpay\MgwPhpSdk\Interfaces\HeidelpayParentInterface;
use heidelpay\MgwPhpSdk\Resources\Customer;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;

trait CanDirectChargeWithCustomer
{
    /**
     * Charge an amount with the given currency.
     * Throws HeidelpayApiException if the transaction could not be performed (e. g. increased risk etc.).
     *
     * @param $amount
     * @param $currency
     * @param $returnUrl
     * @param Customer|string $customer
     * @param null            $orderId
     *
     * @return Charge
     *
     * @throws \RuntimeException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException
     * @throws \heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException
     */
    public function charge($amount, $currency, $returnUrl, $customer, $orderId = null): Charge
    {
        if ($this instanceof HeidelpayParentInterface) {
            return $this->getHeidelpayObject()->charge($amount, $currency, $this, $returnUrl, $customer, $orderId);
        }

        throw new HeidelpaySdkException(
            self::class . ' must implement HeidelpayParentInterface to enable ' . __METHOD__ . ' transaction.'
        );
    }
}
