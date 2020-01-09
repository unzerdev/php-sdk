<?php
/**
 * This defines a base class for all payment types e.g. Card, GiroPay, etc.
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
 * @package  heidelpayPHP\PaymentTypes
 */
namespace heidelpayPHP\Resources\PaymentTypes;

use heidelpayPHP\Resources\AbstractHeidelpayResource;

abstract class BasePaymentType extends AbstractHeidelpayResource
{
    //<editor-fold desc="Overridable Methods">

    /**
     * {@inheritDoc}
     */
    protected function getResourcePath(): string
    {
        return 'types/' . parent::getResourcePath();
    }

    /**
     * Returns an array containing additional parameters which are to be exposed within
     * authorize and charge transactions of the payment method.
     *
     * @return array
     */
    public function getTransactionParams(): array
    {
        return [];
    }

    //</editor-fold>
}
