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
        $this->assertNull($customer->getCustomerId());
        $this->assertNull($customer->getFirstname());
        $this->assertNull($customer->getLastname());
        $this->assertNull($customer->getBirthDate());
        $this->assertNull($customer->getPhone());
        $this->assertNull($customer->getMobile());
        $this->assertNull($customer->getEmail());
        $this->assertNull($customer->getCompany());

        $customer->setId('CustomerId-123');
        $this->assertEquals('CustomerId-123', $customer->getId());

        $customer->setFetchedAt(new \dateTime('2018-12-03'));
        $this->assertEquals(new \dateTime('2018-12-03'), $customer->getFetchedAt());

        $customer->setCustomerId('MyCustomerId-123');
        $this->assertEquals('MyCustomerId-123', $customer->getCustomerId());

        $customer->setFirstname('Peter');
        $this->assertEquals('Peter', $customer->getFirstname());

        $customer->setLastname('Universum');
        $this->assertEquals('Universum', $customer->getLastname());

        $customer->setBirthDate(new \DateTime('1982-11-25'));
        $this->assertEquals(new \DateTime('1982-11-25'), $customer->getBirthDate());

        $customer->setPhone('1234567890');
        $this->assertEquals('1234567890', $customer->getPhone());

        $customer->setMobile('01731234567');
        $this->assertEquals('01731234567', $customer->getMobile());

        $customer->setEmail('peter.universum@universum-group.de');
        $this->assertEquals('peter.universum@universum-group.de', $customer->getEmail());

        $customer->setCompany('heidelpay GmbH');
        $this->assertEquals('heidelpay GmbH', $customer->getCompany());
    }
}
