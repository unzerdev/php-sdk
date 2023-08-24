<?php
/**
 * Represents the geo location of an entity.
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

class GeoLocation extends AbstractUnzerResource
{
    /** @var string|null $clientIp */
    private $clientIp;

    /** @var string|null $countryCode */
    private $countryCode;

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
    protected function setClientIp(?string $clientIp): GeoLocation
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
    protected function setCountryCode(?string $countryCode): GeoLocation
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    /**
     * @param string|null $countryCode
     *
     * @return GeoLocation
     */
    protected function setCountryIsoA2(?string $countryCode): GeoLocation
    {
        return $this->setCountryCode($countryCode);
    }
}
