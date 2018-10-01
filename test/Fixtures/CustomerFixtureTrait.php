<?php
/**
 * This trait adds customer fixtures to test classes.
 *
 * LICENSE
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/ngw_sdk/tests/fixtures
 */

namespace heidelpay\NmgPhpSdk\test\Fixtures;

use heidelpay\NmgPhpSdk\Address;
use heidelpay\NmgPhpSdk\Constants\Salutation;
use heidelpay\NmgPhpSdk\Resources\Customer;

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
