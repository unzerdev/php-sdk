<?php
/**
 * This class defines unit tests to verify functionality of EPS payment type.
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
namespace heidelpayPHP\test\unit\Resources\PaymentTypes;

use heidelpayPHP\Resources\PaymentTypes\EPS;
use heidelpayPHP\test\BaseUnitTest;

class EPSTest extends BaseUnitTest
{
    /**
     * Verify getters and setters work as expected.
     *
     * @test
     *
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \PHPUnit\Framework\Exception
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $eps = new EPS();
        $this->assertNull($eps->getBic());
        $eps->setBic('12345676XXX');
        $this->assertEquals('12345676XXX', $eps->getBic());
    }
}
