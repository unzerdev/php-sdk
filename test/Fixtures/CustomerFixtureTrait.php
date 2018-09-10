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
            ->setCompany('Musterfirma')
            ->setStreet1('Märchenstraße 3')
            ->setStreet1('Hinterhaus')
            ->setCity('Pusemuckel')
            ->setCountry('Deutschland')
            ->setZip('12345')
            ->setBirthday('2018-08-12')
            ->setEmail('max@mustermann.de');
    }
}
