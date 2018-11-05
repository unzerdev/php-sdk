<?php
/**
 * This trait adds customer fixtures to test classes.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/tests/fixtures
 */
namespace heidelpay\MgwPhpSdk\test\Fixtures;

use heidelpay\MgwPhpSdk\Constants\Salutations;
use heidelpay\MgwPhpSdk\Resources\Address;
use heidelpay\MgwPhpSdk\Resources\Customer;

trait CustomerFixtureTrait
{
    /**
     * Create a customer object with just firstname and lastname.
     *
     * @return Customer
     */
    public function getMinimalCustomer(): Customer
    {
        return new Customer('Max', 'Mustermann');
    }

    /**
     * Creates a customer object
     *
     * @return Customer
     */
    public function getMaximumCustomer(): Customer
    {
        return (new Customer())
            ->setFirstname('Peter')
            ->setLastname('Universum')
            ->setSalutation(Salutations::MR)
            ->setCompany('heidelpay GmbH')
            ->setBirthDate('1989-12-24')
            ->setEmail('peter.universum@universum-group.de')
            ->setMobile('+49172123456')
            ->setPhone('+4962216471100')
            ->setBillingAddress($this->getAddress());
    }

    /**
     * Creates a customer object with shippingAddress
     *
     * @return Customer
     */
    public function getMaximumCustomerInclShippingAddress(): Customer
    {
        return $this->getMaximumCustomer()->setShippingAddress($this->getAddress());
    }

    /**
     * Create a test Address
     *
     * @return Address
     */
    public function getAddress(): Address
    {
        return (new Address())
            ->setName('Peter Universum')
            ->setStreet('Hugo-Junkers-Str. 5')
            ->setZip('60386')
            ->setCity('Frankfurt am Main')
            ->setCountry('DE')
            ->setState('DE-BO');
    }
}
