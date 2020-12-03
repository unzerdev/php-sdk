<?php
/**
 * This defines a base class for all payment types e.g. Card, GiroPay, etc.
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
 * @package  UnzerSDK\PaymentTypes
 */
namespace UnzerSDK\Resources\PaymentTypes;

use UnzerSDK\Adapter\HttpAdapterInterface;
use UnzerSDK\Resources\AbstractUnzerResource;

abstract class BasePaymentType extends AbstractUnzerResource
{
    /**
     * Return true for invoice types.
     * This enables you to handle the invoice workflow correctly.
     * Special to these payment types is that the initial charge transaction never changes from pending to success.
     * And that shipment is done before payment is complete.
     * Pending state of initial transaction can be viewed as successful and can be handled as such.
     *
     * @return bool
     */
    public function isInvoiceType(): bool
    {
        return false;
    }

    //<editor-fold desc="Overridable Methods">

    /**
     * {@inheritDoc}
     */
    protected function getResourcePath($httpMethod = HttpAdapterInterface::REQUEST_GET): string
    {
        $path = 'types';
        if ($httpMethod !== HttpAdapterInterface::REQUEST_GET || $this->id === null) {
            $path .= '/' . parent::getResourcePath($httpMethod);
        }

        return $path;
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
