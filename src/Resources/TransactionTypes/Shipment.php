<?php
/**
 * This represents the shipment transaction.
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
 * @package  UnzerSDK\TransactionTypes
 */
namespace UnzerSDK\Resources\TransactionTypes;

use UnzerSDK\Adapter\HttpAdapterInterface;
use UnzerSDK\Traits\HasInvoiceId;

class Shipment extends AbstractTransactionType
{
    use HasInvoiceId;

    /** @var float|null $amount */
    protected $amount;

    //<editor-fold desc="Getters/Setters">

    /**
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     *
     * @return Shipment
     */
    public function setAmount($amount): Shipment
    {
        $this->amount = $amount !== null ? round($amount, 4) : null;
        return $this;
    }

    //</editor-fold>

    //<editor-fold desc="Overridable methods">

    /**
     * {@inheritDoc}
     */
    protected function getResourcePath($httpMethod = HttpAdapterInterface::REQUEST_GET): string
    {
        return 'shipments';
    }

    //</editor-fold>
}
