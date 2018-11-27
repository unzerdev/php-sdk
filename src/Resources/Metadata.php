<?php
/**
 * This represents the customer resource.
 *
 * Copyright (C) 2018 Heidelpay GmbH
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

use heidelpay\MgwPhpSdk\Heidelpay;

class Metadata extends AbstractHeidelpayResource
{
    private $metadata = [];

    protected $shopType = '';
    protected $shopVersion = '';
    protected $sdkType = Heidelpay::SDK_TYPE;
    protected $sdkVersion = Heidelpay::SDK_VERSION;

    //<editor-fold desc="Setters/Getters">

    /**
     * @return string
     */
    public function getShopType(): string
    {
        return $this->shopType;
    }

    /**
     * @param string $shopType
     *
     * @return Metadata
     */
    public function setShopType(string $shopType): Metadata
    {
        $this->shopType = $shopType;
        return $this;
    }

    /**
     * @return string
     */
    public function getShopVersion(): string
    {
        return $this->shopVersion;
    }

    /**
     * @param string $shopVersion
     *
     * @return Metadata
     */
    public function setShopVersion(string $shopVersion): Metadata
    {
        $this->shopVersion = $shopVersion;
        return $this;
    }

    /**
     * @return string
     */
    public function getSdkType(): string
    {
        return $this->sdkType;
    }

    /**
     * @return string
     */
    public function getSdkVersion(): string
    {
        return $this->sdkVersion;
    }

    /**
     * Magic setter for custom data (aka: Criterion).
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (\in_array($name, ['SdkVersion', 'SdkType', 'ShopType', 'ShopVersion'])) {
            return;
        }

        $this->metadata[$name] = $value;
    }

    /**
     * Magic getter for custom data (aka: Criterion).
     *
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (!$this->__isset($name)) {
            return null;
        }

        return $this->metadata[$name];
    }

    /**
     * Magic isset method to check whether the given variable is set.
     *
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->metadata[$name]);
    }

    //</editor-fold>
}
