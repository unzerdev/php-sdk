<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This trait adds customer fixtures to test classes.
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
 * @package  UnzerSDK\test\Fixtures
 */

namespace UnzerSDK\test\Fixtures;

use UnzerSDK\Constants\CompanyCommercialSectorItems;
use UnzerSDK\Constants\CompanyTypes;
use UnzerSDK\Constants\Salutations;
use UnzerSDK\Constants\ShippingTypes;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\CustomerFactory;
use UnzerSDK\Resources\EmbeddedResources\Address;
use UnzerSDK\Resources\EmbeddedResources\CompanyOwner;

trait CustomerFixtureTrait
{
    /**
     * Create a customer object with just firstname and lastname.
     *
     * @return Customer
     */
    public function getMinimalCustomer(): Customer
    {
        return CustomerFactory::createCustomer('Max', 'Mustermann');
    }

    /**
     * Creates a customer object
     *
     * @return Customer
     */
    public function getMaximumCustomer(): Customer
    {
        return CustomerFactory::createCustomer('Peter', 'Universum')
            ->setSalutation(Salutations::MR)
            ->setCompany('Unzer GmbH')
            ->setBirthDate('1989-12-24')
            ->setEmail('peter.universum@universum-group.de')
            ->setMobile('+49172123456')
            ->setPhone('+4962216471100')
            ->setBillingAddress($this->getBillingAddress())
            ->setParentResource($this->unzer);
    }

    /**
     * Creates a customer object with shippingAddress
     *
     * @return Customer
     */
    public function getMaximumCustomerInclShippingAddress(): Customer
    {
        return $this->getMaximumCustomer()->setShippingAddress($this->getShippingAddress());
    }

    /**
     * Creates a not registered B2B customer object
     *
     * @return Customer
     */
    public function getMinimalNotRegisteredB2bCustomer(): Customer
    {
        return CustomerFactory::createNotRegisteredB2bCustomer(
            'Max',
            'Mustermann',
            '2001-12-12',
            $this->getBillingAddress(),
            'test@test.de',
            'Unzer GmbH',
            CompanyCommercialSectorItems::WAREHOUSING_AND_SUPPORT_ACTIVITIES_FOR_TRANSPORTATION
        );
    }

    /**
     * Creates a not registered B2B customer object
     *
     * @return Customer
     */
    public function getMaximalNotRegisteredB2bCustomer(): Customer
    {
        $customer = $this->getMinimalNotRegisteredB2bCustomer()
            ->setShippingAddress($this->getShippingAddress())
            ->setSalutation(Salutations::MR)
            ->setMobile('+49172123456')
            ->setPhone('+4962216471100')
            ->setBillingAddress($this->getBillingAddress());

        $owner = (new CompanyOwner())
            ->setFirstname('firstname')
            ->setLastname('lastname')
            ->setBirthdate('1999-01-01');

        $customer->getCompanyInfo()
            ->setOwner($owner)
            ->setCompanyType(CompanyTypes::COMPANY);

        return $customer;
    }

    /**
     * Creates a registered B2B customer object
     *
     * @return Customer
     */
    public function getMinimalRegisteredB2bCustomer(): Customer
    {
        return CustomerFactory::createRegisteredB2bCustomer($this->getBillingAddress(), '123456789', 'Unzer GmbH');
    }

    /**
     * Creates a registered B2B customer object
     *
     * @return Customer
     */
    public function getMaximalRegisteredB2bCustomer(): Customer
    {
        $customer = $this->getMinimalRegisteredB2bCustomer()
            ->setShippingAddress($this->getShippingAddress())
            ->setSalutation(Salutations::MR)
            ->setMobile('+49172123456')
            ->setPhone('+4962216471100')
            ->setBillingAddress($this->getBillingAddress());

        $owner = (new CompanyOwner())
            ->setFirstname('firstname')
            ->setLastname('lastname')
            ->setBirthdate('1999-01-01');

        $customer->getCompanyInfo()
            ->setOwner($owner)
            ->setCompanyType(CompanyTypes::COMPANY);

        return $customer;
    }

    /**
     * Create a test Address
     *
     * @return Address
     */
    public function getBillingAddress(): Address
    {
        return (new Address())
            ->setName('Peter Universum')
            ->setStreet('Hugo-Junkers-Str. 5')
            ->setZip('60386')
            ->setCity('Frankfurt am Main')
            ->setCountry('DE')
            ->setState('DE-BO');
    }

    /**
     * Create a test Address
     *
     * @return Address
     */
    public function getShippingAddress(): Address
    {
        return (new Address())
            ->setName('Max Universum')
            ->setStreet('Hugo-Junkers-Str. 4')
            ->setZip('60386')
            ->setCity('Frankfurt am Main')
            ->setCountry('DE')
            ->setState('DE-BO')
            ->setShippingType(ShippingTypes::EQUALS_BILLING);
    }
}
