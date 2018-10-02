<?php
/**
 * This class defines integration tests to verify interface and
 * functionality of the Customer resource.
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
 * @package  heidelpay/mgw_sdk/tests/integration
 */
namespace heidelpay\MgwPhpSdk\test;

use heidelpay\MgwPhpSdk\Constants\ApiResponseCodes;
use heidelpay\MgwPhpSdk\Constants\Currency;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Resources\Customer;
use heidelpay\MgwPhpSdk\Resources\Payment;

class CustomerTest extends BasePaymentTest
{
    /**
     * Min customer should be creatable via the sdk.
     *
     * @test
     */
    public function minCustomerCanBeCreatedAndFetched(): Customer
    {
        /** @var Customer $customer */
        $customer = $this->getMinimalCustomer();
        $this->assertEmpty($customer->getId());
        $this->heidelpay->createCustomer($customer);
        $this->assertNotEmpty($customer->getId());

        /** @var Customer $fetchedCustomer */
        $fetchedCustomer = $this->heidelpay->fetchCustomerById($customer->getId());
        $this->assertEquals($customer, $fetchedCustomer);

        return $customer;
    }

    /**
     * Max customer should be creatable via the sdk.
     *
     * @test
     */
    public function maxCustomerCanBeCreatedAndFetched(): Customer
    {
        $customer = $this->getMaximumCustomer();
        $this->assertEmpty($customer->getId());
        $this->heidelpay->createCustomer($customer);
        $this->assertNotEmpty($customer->getId());

        /** @var Customer $fetchedCustomer */
        $fetchedCustomer = $this->heidelpay->fetchCustomerById($customer->getId());
        $this->assertEquals($customer, $fetchedCustomer);

        return $customer;
    }

    /**
     * @param Customer $customer
     * @depends maxCustomerCanBeCreatedAndFetched
     * @test
     */
    public function customerCanBeFetched(Customer $customer)
    {
        $fetchedCustomer = $this->heidelpay->fetchCustomerById($customer->getId());
        $this->assertEquals($customer->getId(), $fetchedCustomer->getId());
    }

    /**
     * Customer can be referenced by payment.
     *
     * @test
     */
    public function transactionShouldCreateAndReferenceCustomerIfItDoesNotExistYet()
    {
        $customer = $this->getMaximumCustomer();
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $authorization = $card->authorize(12.0, Currency::EUROPEAN_EURO, self::RETURN_URL, $customer);

        /** @var Payment $secPayment */
        $secPayment = $this->heidelpay->fetchPaymentById($authorization->getPayment()->getId());

        /** @var Customer $secCustomer */
        $secCustomer = $secPayment->getCustomer();
        $this->assertNotNull($secCustomer);
        $this->assertEquals($customer, $secCustomer);
    }

    /**
     * Customer can be referenced by payment.
     *
     * @test
     */
    public function transactionShouldReferenceCustomerIfItExist()
    {
        $customer = $this->getMaximumCustomer();
        $this->heidelpay->createCustomer($customer);
        $card = $this->heidelpay->createPaymentType($this->createCard());
        $authorization = $card->authorize(12.0, Currency::EUROPEAN_EURO, self::RETURN_URL, $customer);

        /** @var Payment $secPayment */
        $secPayment = $this->heidelpay->fetchPaymentById($authorization->getPayment()->getId());

        /** @var Customer $secCustomer */
        $secCustomer = $secPayment->getCustomer();
        $this->assertNotNull($secCustomer);
        $this->assertEquals($customer, $secCustomer);
    }

    /**
     * Customer can be updated.
     *
     * @depends maxCustomerCanBeCreatedAndFetched
     * @test
     * @param Customer $customer
     */
    public function customerShouldBeUpdatable(Customer $customer)
    {
		$this->assertEquals($customer->getFirstname(), 'Max');
        $customer->setFirstname('Not Max');
        $this->heidelpay->updateCustomer($customer);
        $this->assertEquals($customer->getFirstname(), 'Not Max');

        /** @var Customer $fetchedCustomer */
        $fetchedCustomer = $this->heidelpay->fetchCustomerById($customer->getId());
		$this->assertEquals($customer->getId(), $fetchedCustomer->getId());
		$this->assertEquals('Not Max', $fetchedCustomer->getFirstname());
    }

    /**
     * Customer can be deleted.
     *
     * @depends maxCustomerCanBeCreatedAndFetched
     * @test
     * @param Customer $customer
     */
    public function customerShouldBeDeletable(Customer $customer)
    {
        $this->assertNotNull($customer);
        $this->assertNotNull($customer->getId());

        $this->heidelpay->deleteCustomerById($customer->getId());

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_CUSTOMER_DOES_NOT_EXIST);
        $this->heidelpay->fetchCustomerById($customer->getId());
    }
}
