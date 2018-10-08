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
 *
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/tests/fixtures
 */

namespace heidelpay\MgwPhpSdk\test\Fixtures;

use heidelpay\MgwPhpSdk\Constants\Salutation;
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
            ->setFirstname('Max')
            ->setLastname('Mustermann')
            ->setSalutation(Salutation::MR)
            ->setCompany('Musterfirma')
            ->setBirthDate('1982-08-12')
            ->setEmail('max@mustermann.de')
            ->setMobile('01731234567')
            ->setPhone('062216471400')
            ->setBillingAddress($this->getAddress());
    }

    /**
     * Create a test Address
     *
     * @return Address
     */
    public function getAddress(): Address
    {
        return (new Address())
            ->setName('Max Mustermann')
            ->setStreet('Vangerowstr. 18')
            ->setZip('69115')
            ->setCity('Heidelberg')
            ->setCountry('DE')
            ->setState('DE-1');
    }
}
