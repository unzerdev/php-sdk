<?php
/**
 * Represents the message resource holding information like a transaction error code and message.
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
 * @package  UnzerSDK\Resources\EmbeddedResources
 */
namespace UnzerSDK\Resources\EmbeddedResources;

use UnzerSDK\Resources\AbstractUnzerResource;

class Message extends AbstractUnzerResource
{
    /** @var string $code */
    private $code = '';

    /** @var string $customer */
    private $customer = '';

    /** @var string $merchant */
    private $merchant = '';

    //<editor-fold desc="Getters/Setters">

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return Message
     */
    protected function setCode(string $code): Message
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomer(): string
    {
        return $this->customer;
    }

    /**
     * @param string $customer
     *
     * @return Message
     */
    protected function setCustomer(string $customer): Message
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMerchant(): ?string
    {
        return $this->merchant;
    }

    /**
     * @param string|null $merchant
     *
     * @return Message
     */
    protected function setMerchant(?string $merchant): Message
    {
        $this->merchant = $merchant;
        return $this;
    }

    //</editor-fold>
}
