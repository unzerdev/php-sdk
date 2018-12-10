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
 * @package  heidelpayPHP/resources
 */
namespace heidelpayPHP\Resources;

class Keypair extends AbstractHeidelpayResource
{
    /** @var string $publicKey */
    private $publicKey;

    /** @var string $privateKey */
    private $privateKey;

    /** @var array $availablePaymentTypes */
    private $availablePaymentTypes = [];

    //<editor-fold desc="Getters/Setters">

    /**
     * @return string|null
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     */
    protected function setPublicKey(string $publicKey)
    {
        $this->publicKey = $publicKey;
    }

    /**
     * @return string|null
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @param string $privateKey
     */
    protected function setPrivateKey(string $privateKey)
    {
        $this->privateKey = $privateKey;
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
    protected function setAvailablePaymentTypes(array $paymentTypes)
    {
        $this->availablePaymentTypes = $paymentTypes;
    }

    //</editor-fold>
}
