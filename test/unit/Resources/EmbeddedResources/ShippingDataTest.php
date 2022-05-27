<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the embedded shipping resource.
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
 * @package  UnzerSDK\test\unit
 */
namespace UnzerSDK\test\unit\Resources\EmbeddedResources;

use UnzerSDK\Resources\EmbeddedResources\ShippingData;
use UnzerSDK\test\BasePaymentTest;

class ShippingDataTest extends BasePaymentTest
{
    /**
     * Verify setter and getter functionalities.
     *
     * @test
     *
     */
    public function settersAndGettersShouldWork(): void
    {
        $shipping = new ShippingData();
        $this->assertNull($shipping->getDeliveryService());
        $this->assertNull($shipping->getDeliveryTrackingId());
        $this->assertNull($shipping->getReturnTrackingId());

        $resp = [
            "deliveryTrackingId"=> "deliveryTrackingId",
            "deliveryService"=>"deliveryService",
            "returnTrackingId"=>"returnTrackingId",
        ];
        $shipping->handleResponse((object)$resp);

        $this->assertEquals('deliveryTrackingId', $shipping->getDeliveryTrackingId());
        $this->assertEquals('deliveryService', $shipping->getDeliveryService());
        $this->assertEquals('returnTrackingId', $shipping->getReturnTrackingId());
    }
}
