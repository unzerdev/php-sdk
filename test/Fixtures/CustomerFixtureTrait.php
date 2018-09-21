<?php
/**
 * Description
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/${Package}
 */

namespace heidelpay\NmgPhpSdk\test\Fixtures;

use heidelpay\NmgPhpSdk\Address;
use heidelpay\NmgPhpSdk\Constants\Salutation;
use heidelpay\NmgPhpSdk\Customer;

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
