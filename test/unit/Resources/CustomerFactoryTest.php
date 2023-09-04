<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the customer factory.
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

namespace UnzerSDK\test\unit\Resources;

use UnzerSDK\Constants\CompanyCommercialSectorItems;
use UnzerSDK\Constants\CompanyRegistrationTypes;
use UnzerSDK\Resources\CustomerFactory;
use UnzerSDK\Resources\EmbeddedResources\Address;
use UnzerSDK\Resources\EmbeddedResources\CompanyInfo;
use PHPUnit\Framework\TestCase;

class CustomerFactoryTest extends TestCase
{
    /**
     * Verify that the factory creates the B2C customer object as desired.
     *
     * @test
     */
    public function b2cCustomerIsCreatedAsExpected(): void
    {
        $customer = CustomerFactory::createCustomer('Max', 'Mustermann');
        $this->assertEquals('Max', $customer->getFirstname());
        $this->assertEquals('Mustermann', $customer->getLastname());
        $this->assertNull($customer->getCompanyInfo());
    }

    /**
     * Verify that the factory creates the registered B2B customer object as desired.
     *
     * @test
     */
    public function registeredB2bCustomerIsCreatedAsExpected(): void
    {
        $address = new Address();

        $customer = CustomerFactory::createRegisteredB2bCustomer(
            $address,
            '123',
            'abc GmbH',
            CompanyCommercialSectorItems::ACCOMMODATION
        );

        $this->assertSame($address, $customer->getBillingAddress());
        $this->assertEquals('abc GmbH', $customer->getCompany());
        $companyInfo = $customer->getCompanyInfo();
        $this->assertInstanceOf(CompanyInfo::class, $companyInfo);
        $this->assertEquals(CompanyRegistrationTypes::REGISTRATION_TYPE_REGISTERED, $companyInfo->getRegistrationType());
        $this->assertEquals(CompanyCommercialSectorItems::ACCOMMODATION, $companyInfo->getCommercialSector());
        $this->assertEquals('123', $companyInfo->getCommercialRegisterNumber());
    }

    /**
     * Verify that the factory creates the not registered B2B customer object as desired.
     *
     * @test
     */
    public function notRegisteredB2bCustomerIsCreatedAsExpected(): void
    {
        $address = new Address();

        $customer = CustomerFactory::createNotRegisteredB2bCustomer(
            'Max',
            'Mustermann',
            '2000-12-12',
            $address,
            'test@unzer.com',
            'abc GmbH',
            CompanyCommercialSectorItems::ACCOMMODATION
        );

        $this->assertSame($address, $customer->getBillingAddress());
        $this->assertEquals('Max', $customer->getFirstname());
        $this->assertEquals('Mustermann', $customer->getLastname());
        $this->assertEquals('2000-12-12', $customer->getBirthDate());
        $this->assertEquals('test@unzer.com', $customer->getEmail());
        $this->assertEquals('abc GmbH', $customer->getCompany());
        $companyInfo = $customer->getCompanyInfo();
        $this->assertInstanceOf(CompanyInfo::class, $companyInfo);
        $this->assertEquals(CompanyRegistrationTypes::REGISTRATION_TYPE_NOT_REGISTERED, $companyInfo->getRegistrationType());
        $this->assertEquals(CompanyCommercialSectorItems::ACCOMMODATION, $companyInfo->getCommercialSector());
    }
}
