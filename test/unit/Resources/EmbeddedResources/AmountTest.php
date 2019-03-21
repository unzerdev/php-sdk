<?php
/**
 * This class defines unit tests to verify functionality of the embedded Amount resource.
 *
 * Copyright (C) 2018 heidelpay GmbH
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
 * @package  heidelpayPHP/test/unit
 */
namespace heidelpayPHP\test\unit\Resources;

use heidelpayPHP\Resources\EmbeddedResources\Amount;
use heidelpayPHP\test\BaseUnitTest;
use PHPUnit\Framework\Exception;

class AmountTest extends BaseUnitTest
{
    /**
     * Verify setter and getter functionalities.
     *
     * @test
     *
     * @throws Exception
     *
     */
    public function settersAndGettersShouldWork()
    {
        $amount = new Amount();
        $this->assertNull($amount->getCurrency());
        $this->assertEquals(0.0, $amount->getTotal());
        $this->assertEquals(0.0, $amount->getCanceled());
        $this->assertEquals(0.0, $amount->getCharged());
        $this->assertEquals(0.0, $amount->getRemaining());

        $amount->setTotal(1.1);
        $amount->setCanceled(2.2);
        $amount->setCharged(3.3);
        $amount->setRemaining(4.4);
        $amount->setCurrency('MyCurrency');

        $this->assertEquals('MyCurrency', $amount->getCurrency());
        $this->assertEquals(1.1, $amount->getTotal());
        $this->assertEquals(2.2, $amount->getCanceled());
        $this->assertEquals(3.3, $amount->getCharged());
        $this->assertEquals(4.4, $amount->getRemaining());
    }
}
