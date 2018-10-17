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
        $firstname = 'Max';
        $lastname  = 'Mustermann';
        return (new Customer())
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setSalutation(Salutations::MR)
            ->setCompany('Musterfirma')
            ->setBirthDate('1982-08-12')
            ->setEmail('max@mustermann.de')
            ->setMobile('01731234567')
            ->setPhone('062216471400')
            ->setBillingAddress($this->getAddress($firstname, $lastname));
    }

    /**
     * Creates a customer object
     *
     * @return Customer
     */
    public function getSepaDirectDebitGuaranteedCustomer(): Customer
    {
        $firstname = 'Peter';
        $lastname  = 'Universum';
        return (new Customer())
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setSalutation(Salutations::MR)
            ->setBirthDate('1989-12-24')
            ->setEmail('peter.universum@universum-group.de')
            ->setBillingAddress($this->getSepaDirectDebitGuaranteedAddress($firstname, $lastname));
    }

    /**
     * Create a test Address
     *
     * @param string $firstname
     * @param string $lastname
     *
     * @return Address
     */
    public function getAddress($firstname, $lastname): Address
    {
        return (new Address())
            ->setName($firstname . ' ' . $lastname)
            ->setStreet('Vangerowstr. 18')
            ->setZip('69115')
            ->setCity('Heidelberg')
            ->setCountry('DE')
            ->setState('DE-1');
    }

    /**
     * Create a test address for Universum customer.
     *
     * @param string $firstname
     * @param string $lastname
     *
     * @return Address
     */
    public function getSepaDirectDebitGuaranteedAddress($firstname, $lastname): Address
    {
        return (new Address())
            ->setName($firstname . ' ' . $lastname)
            ->setStreet('Hugo-Junkers-Str. 5')
            ->setZip('60386')
            ->setCity('Frankfurt am Main')
            ->setCountry('DE')
            ->setState('DE-1');
    }
}
