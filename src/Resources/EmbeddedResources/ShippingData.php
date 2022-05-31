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
 * @package  UnzerSDK\Resources\EmbeddedResources
 */
namespace UnzerSDK\Resources\EmbeddedResources;

use UnzerSDK\Resources\AbstractUnzerResource;

class ShippingData extends AbstractUnzerResource
{
    /** @var string|null $deliveryTrackingId */
    protected $deliveryTrackingId;

    /** @var string|null $deliveryService */
    protected $deliveryService;

    /** @var string|null $returnTrackingId */
    protected $returnTrackingId;

    /**
     * @return string|null
     */
    public function getDeliveryTrackingId(): ?string
    {
        return $this->deliveryTrackingId;
    }

    /**
     * @param string|null $deliveryTrackingId
     *
     * @return ShippingData
     */
    public function setDeliveryTrackingId(?string $deliveryTrackingId): ShippingData
    {
        $this->deliveryTrackingId = $deliveryTrackingId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDeliveryService(): ?string
    {
        return $this->deliveryService;
    }

    /**
     * @param string|null $deliveryService
     *
     * @return ShippingData
     */
    public function setDeliveryService(?string $deliveryService): ShippingData
    {
        $this->deliveryService = $deliveryService;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getReturnTrackingId(): ?string
    {
        return $this->returnTrackingId;
    }

    /**
     * @param string|null $returnTrackingId
     *
     * @return ShippingData
     */
    public function setReturnTrackingId(?string $returnTrackingId): ShippingData
    {
        $this->returnTrackingId = $returnTrackingId;
        return $this;
    }
}
