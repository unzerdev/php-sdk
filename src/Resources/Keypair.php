<?php
/**
 * This represents the key pair resource.
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
 * @package  heidelpay/mgw_sdk/resources
 */
namespace heidelpay\MgwPhpSdk\Resources;

class Keypair extends AbstractHeidelpayResource
{
    /** @var string $publicKey */
    private $publicKey;

    /** @var array $availablePaymentTypes */
    private $availablePaymentTypes = [];

    //<editor-fold desc="Getters/Setters">

    /**
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     */
    public function setPublicKey(string $publicKey)
    {
        $this->publicKey = $publicKey;
    }

    /**
     * @return array
     */
    public function getAvailablePaymentTypes(): array
    {
        return $this->availablePaymentTypes;
    }

    /**
     * @param array $paymentTypes
     */
    public function setAvailablePaymentTypes(array $paymentTypes)
    {
        $this->availablePaymentTypes = $paymentTypes;
    }

    //</editor-fold>
}
