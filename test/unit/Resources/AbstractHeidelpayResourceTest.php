<?php
/**
 * This class defines unit tests to verify functionality of the AbstractHeidelpayResource.
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
 * @package  heidelpay/mgw_sdk/test/unit
 */
namespace heidelpay\MgwPhpSdk\test\unit\Resources;

use heidelpay\MgwPhpSdk\Resources\Customer;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class AbstractHeidelpayResourceTest extends TestCase
{
    /**
     * Verify setter and getter functionality.
     *
     * @test
     *
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function settersAndGettersShouldWork()
    {
        $customer = new Customer();
        $this->assertNull($customer->getId());
        $this->assertNull($customer->getFetchedAt());

        $customer->setId('CustomerId-123');
        $this->assertEquals('CustomerId-123', $customer->getId());

        $customer->setFetchedAt(new \dateTime('2018-12-03'));
        $this->assertEquals(new \dateTime('2018-12-03'), $customer->getFetchedAt());
    }
}
