<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the embedded Amount resource.
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

use UnzerSDK\Resources\EmbeddedResources\Amount;
use UnzerSDK\test\BasePaymentTest;

class AmountTest extends BasePaymentTest
{
    /**
     * Verify setter and getter functionalities.
     *
     * @test
     *
     */
    public function settersAndGettersShouldWork(): void
    {
        $amount = new Amount();
        $this->assertNull($amount->getCurrency());
        $this->assertEquals(0.0, $amount->getTotal());
        $this->assertEquals(0.0, $amount->getCanceled());
        $this->assertEquals(0.0, $amount->getCharged());
        $this->assertEquals(0.0, $amount->getRemaining());

        $resp = ['total' => 1.1, 'canceled' => 2.2, 'charged' => 3.3, 'remaining' => 4.4, 'currency' => 'MyCurrency'];
        $amount->handleResponse((object)$resp);

        $this->assertEquals('MyCurrency', $amount->getCurrency());
        $this->assertEquals(1.1, $amount->getTotal());
        $this->assertEquals(2.2, $amount->getCanceled());
        $this->assertEquals(3.3, $amount->getCharged());
        $this->assertEquals(4.4, $amount->getRemaining());
    }
}
