<?php
/**
 * Represents the Applepay header resource.
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
 * @author  David Owusu <development@unzer.com>
 *
 * @package  UnzerSDK\Resources\EmbeddedResources
 */
namespace UnzerSDK\Resources\EmbeddedResources;

use UnzerSDK\Resources\AbstractUnzerResource;

class ApplePayHeader extends AbstractUnzerResource
{
    /** @var string|null */
    protected $ephemeralPublicKey;

    /** @var string|null */
    protected $publicKeyHash;

    /** @var string|null */
    protected $transactionId;

    /**
     * ApplePayHeader constructor.
     *
     * @param string|null $ephemeralPublicKey
     * @param string|null $publicKeyHash
     * @param string|null $transactionId
     */
    public function __construct(?string $ephemeralPublicKey, ?string $publicKeyHash, ?string $transactionId = null)
    {
        $this->ephemeralPublicKey = $ephemeralPublicKey;
        $this->publicKeyHash = $publicKeyHash;
        $this->transactionId = $transactionId;
    }

    /**
     * @param string|null $ephemeralPublicKey
     *
     * @return ApplePayHeader
     */
    public function setEphemeralPublicKey(?string $ephemeralPublicKey): ApplePayHeader
    {
        $this->ephemeralPublicKey = $ephemeralPublicKey;
        return $this;
    }

    /**
     * @param string|null $publicKeyHash
     *
     * @return ApplePayHeader
     */
    public function setPublicKeyHash(?string $publicKeyHash): ApplePayHeader
    {
        $this->publicKeyHash = $publicKeyHash;
        return $this;
    }

    /**
     * @param string|null $transactionId
     *
     * @return ApplePayHeader
     */
    public function setTransactionId(?string $transactionId): ApplePayHeader
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEphemeralPublicKey(): ?string
    {
        return $this->ephemeralPublicKey;
    }

    /**
     * @return string|null
     */
    public function getPublicKeyHash(): ?string
    {
        return $this->publicKeyHash;
    }

    /**
     * @return string|null
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }
}
