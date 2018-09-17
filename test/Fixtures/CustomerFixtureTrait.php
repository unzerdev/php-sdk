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
     * Creates a customer object
     *
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        return (new Customer())
            ->setFirstname('Max')
            ->setLastname('Mustermann')
            ->setSalutation(Salutation::MR)
            ->setCompany('Musterfirma')
            ->setBirthday('1982-08-12')
            ->setEmail('max@mustermann.de')
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
