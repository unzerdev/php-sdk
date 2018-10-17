<?php
/**
 * This class defines integration tests to verify interface and
 * functionality of the Customer resource.
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
 * @package  heidelpay/mgw_sdk/tests/integration
 */
namespace heidelpay\MgwPhpSdk\test\integration;

use heidelpay\MgwPhpSdk\Constants\ApiResponseCodes;
use heidelpay\MgwPhpSdk\Constants\Currency;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpaySdkException;
use heidelpay\MgwPhpSdk\Resources\Customer;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Card;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;

class CustomerTest extends BasePaymentTest
{
    /**
     * Min customer should be creatable via the sdk.
     *
     * @test
     *
     * @return Customer
     *
     * @throws HeidelpayApiException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function minCustomerCanBeCreatedAndFetched(): Customer
    {
        /** @var Customer $customer */
        $customer = $this->getMinimalCustomer();
        $this->assertEmpty($customer->getId());
        $this->heidelpay->createCustomer($customer);
        $this->assertNotEmpty($customer->getId());

        /** @var Customer $fetchedCustomer */
        $fetchedCustomer = $this->heidelpay->fetchCustomer($customer->getId());
        $this->assertEquals($customer->expose(), $fetchedCustomer->expose());

        return $customer;
    }

    /**
     * Max customer should be creatable via the sdk.
     *
     * @test
     *
     * @return Customer
     *
     * @throws HeidelpayApiException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function maxCustomerCanBeCreatedAndFetched(): Customer
    {
        $customer = $this->getMaximumCustomer();
        $this->assertEmpty($customer->getId());
        $this->heidelpay->createCustomer($customer);
        $this->assertNotEmpty($customer->getId());

        /** @var Customer $fetchedCustomer */
        $fetchedCustomer = $this->heidelpay->fetchCustomer($customer->getId());
        $this->assertEquals($customer->expose(), $fetchedCustomer->expose());

        return $customer;
    }

    /**
     * @param Customer $customer
     *
     * @throws HeidelpayApiException
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     * @depends maxCustomerCanBeCreatedAndFetched
     * @test
     */
    public function customerCanBeFetched(Customer $customer)
    {
        $fetchedCustomer = $this->heidelpay->fetchCustomer($customer->getId());
        $this->assertEquals($customer->getId(), $fetchedCustomer->getId());
    }

    /**
     * Customer can be referenced by payment.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function transactionShouldCreateAndReferenceCustomerIfItDoesNotExistYet()
    {
        $customer = $this->getMaximumCustomer();

        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $authorization = $card->authorize(12.0, Currency::EURO, self::RETURN_URL, $customer);

        /** @var Payment $secPayment */
        $secPayment = $this->heidelpay->fetchPaymentById($authorization->getPayment()->getId());

        /** @var Customer $secCustomer */
        $secCustomer = $secPayment->getCustomer();
        $this->assertNotNull($secCustomer);
        $this->assertEquals($customer->expose(), $secCustomer->expose());
    }

    /**
     * Customer can be referenced by payment.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function transactionShouldReferenceCustomerIfItExist()
    {
        $customer = $this->getMaximumCustomer();
        $this->heidelpay->createCustomer($customer);

        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $authorization = $card->authorize(12.0, Currency::EURO, self::RETURN_URL, $customer);

        /** @var Payment $secPayment */
        $secPayment = $this->heidelpay->fetchPaymentById($authorization->getPayment()->getId());

        /** @var Customer $secCustomer */
        $secCustomer = $secPayment->getCustomer();
        $this->assertNotNull($secCustomer);
        $this->assertEquals($customer->expose(), $secCustomer->expose());
    }

    /**
     * Customer can be referenced by payment.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function transactionShouldReferenceCustomerIfItExistAndItsIdHasBeenPassed()
    {
        $customer = $this->getMaximumCustomer();
        $this->heidelpay->createCustomer($customer);

        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $authorization = $card->authorize(12.0, Currency::EURO, self::RETURN_URL, $customer->getId());

        /** @var Payment $secPayment */
        $secPayment = $this->heidelpay->fetchPaymentById($authorization->getPayment()->getId());

        /** @var Customer $secCustomer */
        $secCustomer = $secPayment->getCustomer();
        $this->assertNotNull($secCustomer);
        $this->assertEquals($customer->expose(), $secCustomer->expose());
    }

    /**
     * Customer can be updated.
     *
     * @depends maxCustomerCanBeCreatedAndFetched
     * @test
     *
     * @param Customer $customer
     *
     * @throws HeidelpayApiException
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function customerShouldBeUpdatable(Customer $customer)
    {
        $this->assertEquals($customer->getFirstname(), 'Max');
        $customer->setFirstname('Not Max');
        $this->heidelpay->updateCustomer($customer);
        $this->assertEquals($customer->getFirstname(), 'Not Max');

        /** @var Customer $fetchedCustomer */
        $fetchedCustomer = $this->heidelpay->fetchCustomer($customer->getId());
        $this->assertEquals($customer->getId(), $fetchedCustomer->getId());
        $this->assertEquals('Not Max', $fetchedCustomer->getFirstname());
    }

    /**
     * Customer can be deleted.
     *
     * @depends maxCustomerCanBeCreatedAndFetched
     * @test
     *
     * @param Customer $customer
     *
     * @throws HeidelpayApiException
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     * @throws HeidelpaySdkException
     */
    public function customerShouldBeDeletable(Customer $customer)
    {
        $this->assertNotNull($customer);
        $this->assertNotNull($customer->getId());

        $this->heidelpay->deleteCustomerById($customer->getId());

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_CUSTOMER_DOES_NOT_EXIST);
        $this->heidelpay->fetchCustomer($customer->getId());
    }
}
