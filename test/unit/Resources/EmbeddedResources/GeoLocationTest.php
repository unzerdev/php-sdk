<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the embedded GeoLocation resource.
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
 * @package  UnzerSDK\test\unit
 */

namespace UnzerSDK\test\unit\Resources\EmbeddedResources;

use UnzerSDK\Resources\EmbeddedResources\GeoLocation;
use UnzerSDK\test\BasePaymentTest;

class GeoLocationTest extends BasePaymentTest
{
    /**
     * Verify setter and getter functionalities.
     *
     * @test
     */
    public function settersAndGettersShouldWork(): void
    {
        $geoLocation = new GeoLocation();
        $this->assertNull($geoLocation->getCountryCode());
        $this->assertNull($geoLocation->getClientIp());

        $response = ['countryCode' => 'myCountryCode', 'clientIp' => '127.0.0.1'];
        $geoLocation->handleResponse((object) $response);

        $this->assertEquals('myCountryCode', $geoLocation->getCountryCode());
        $this->assertEquals('127.0.0.1', $geoLocation->getClientIp());

        // Secondary setter works as well
        $response = ['countryIsoA2' => 'differentCountryCode'];
        $geoLocation->handleResponse((object) $response);

        $this->assertEquals('differentCountryCode', $geoLocation->getCountryCode());
    }
}
