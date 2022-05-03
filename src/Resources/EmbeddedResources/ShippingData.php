<?php
/**
 * Shipping class for Paylater payment types.
 *
 * Copyright (C) 2022 - today Unzer E-Com GmbH
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

class ShippingData extends AbstractUnzerResource
{
    /** @var string|null $deliveryTrackingId */
    protected $threatMetrixId;

    /** @var string|null $deliveryService */
    protected $registrationLevel;

    /** @var string|null $returnTrackingId */
    protected $registrationDate;

    /**
     * @return string|null
     */
    public function getThreatMetrixId(): ?string
    {
        return $this->threatMetrixId;
    }

    /**
     * @param string|null $threatMetrixId
     *
     * @return ShippingData
     */
    public function setThreatMetrixId(?string $threatMetrixId): ShippingData
    {
        $this->threatMetrixId = $threatMetrixId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegistrationLevel(): ?string
    {
        return $this->registrationLevel;
    }

    /**
     * @param string|null $registrationLevel
     *
     * @return ShippingData
     */
    public function setRegistrationLevel(?string $registrationLevel): ShippingData
    {
        $this->registrationLevel = $registrationLevel;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegistrationDate(): ?string
    {
        return $this->registrationDate;
    }

    /**
     * @param string|null $registrationDate
     *
     * @return ShippingData
     */
    public function setRegistrationDate(?string $registrationDate): ShippingData
    {
        $this->registrationDate = $registrationDate;
        return $this;
    }
}
