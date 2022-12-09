<?php
/**
 * Represents the address resource.
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

class Address extends AbstractUnzerResource
{
    /** @var string $name */
    protected $name;

    /** @var string $street */
    protected $street;

    /** @var string $state */
    protected $state;

    /** @var string $zip */
    protected $zip;

    /** @var string city */
    protected $city;

    /** @var string country */
    protected $country;

    /** @var string|null shippingType */
    protected $shippingType;

    //<editor-fold desc="Getters/Setters">

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return Address
     */
    public function setName(?string $name): Address
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @param string|null $street
     *
     * @return Address
     */
    public function setStreet(?string $street): Address
    {
        $this->street = $street;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     *
     * @return Address
     */
    public function setState(?string $state): Address
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getZip(): ?string
    {
        return $this->zip;
    }

    /**
     * @param string|null $zip
     *
     * @return Address
     */
    public function setZip(?string $zip): Address
    {
        $this->zip = $zip;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     *
     * @return Address
     */
    public function setCity(?string $city): Address
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     *
     * @return Address
     */
    public function setCountry(?string $country): Address
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getShippingType(): ?string
    {
        return $this->shippingType;
    }

    /**
     * @param string|null $shippingType
     *
     * @return Address
     */
    public function setShippingType(?string $shippingType): Address
    {
        $this->shippingType = $shippingType;
        return $this;
    }

    //</editor-fold>
}
