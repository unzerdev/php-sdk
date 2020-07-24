<?php
/**
 * Represents the geo location of an entity.
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
 * @package  heidelpayPHP\Resources\EmbeddedResources
 */
namespace heidelpayPHP\Resources\EmbeddedResources;

use heidelpayPHP\Resources\AbstractHeidelpayResource;

class GeoLocation extends AbstractHeidelpayResource
{
    /** @var string|null $clientIp */
    private $clientIp;

    /** @var string|null $countryCode */
    private $countryCode;

    //<editor-fold desc="Getters/Setters">

    /**
     * @return string|null
     */
    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    /**
     * @param string|null $clientIp
     *
     * @return GeoLocation
     */
    protected function setClientIp($clientIp): GeoLocation
    {
        $this->clientIp = $clientIp;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * @param string|null $countryCode
     *
     * @return GeoLocation
     */
    protected function setCountryCode($countryCode): GeoLocation
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    /**
     * @param string|null $countryCode
     *
     * @return GeoLocation
     */
    protected function setCountryIsoA2($countryCode): GeoLocation
    {
        return $this->setCountryCode($countryCode);
    }

    //</editor-fold>
}
