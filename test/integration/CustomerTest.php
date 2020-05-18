<?php
/**
 * This class defines integration tests to verify interface and
 * functionality of the Customer resource.
 *
 * Copyright (C) 2018 heidelpay GmbH
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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\test\integration
 */
namespace heidelpayPHP\test\integration;

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Constants\Salutations;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\Paypal;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;
use function microtime;

class CustomerTest extends BasePaymentTest
{
    //<editor-fold desc="General Customer">

    /**
     * Min customer should be creatable via the sdk.
     *
     * @test
     *
     * @return Customer
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function minCustomerCanBeCreatedAndFetched(): Customer
    {
        $customer = $this->getMinimalCustomer();
        $this->assertEmpty($customer->getId());
        $this->heidelpay->createCustomer($customer);
        $this->assertNotEmpty($customer->getId());

        $geoLocation = $customer->getGeoLocation();
        $this->assertNull($geoLocation->getClientIp());
        $this->assertNull($geoLocation->getCountryCode());

        $fetchedCustomer = $this->heidelpay->fetchCustomer($customer->getId());
        $exposeArray     = $customer->expose();
        $exposeArray['salutation'] = Salutations::UNKNOWN;
        $this->assertEquals($exposeArray, $fetchedCustomer->expose());

        $geoLocation = $fetchedCustomer->getGeoLocation();
        $this->assertNotEmpty($geoLocation->getClientIp());
        $this->assertNotEmpty($geoLocation->getCountryCode());

        return $customer;
    }

    /**
     * Max customer should be creatable via the sdk.
     *
     * @test
     *
     * @return Customer
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
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
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     * @depends maxCustomerCanBeCreatedAndFetched
     * @test
     */
    public function customerCanBeFetchedById(Customer $customer)
    {
        $fetchedCustomer = $this->heidelpay->fetchCustomer($customer->getId());
        $this->assertEquals($customer->getId(), $fetchedCustomer->getId());
    }

    /**
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     * @depends maxCustomerCanBeCreatedAndFetched
     * @test
     */
    public function customerCanBeFetchedByCustomerId()
    {
        $customerId = 'c' . self::generateRandomId();
        $customer = $this->getMaximumCustomer()->setCustomerId($customerId);
        $this->heidelpay->createCustomer($customer);

        $fetchedCustomer = $this->heidelpay->fetchCustomerByExtCustomerId($customer->getCustomerId());
        $this->assertEquals($customer->expose(), $fetchedCustomer->expose());
    }

    /**
     * @param Customer $customer
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     * @depends maxCustomerCanBeCreatedAndFetched
     * @test
     */
    public function customerCanBeFetchedByObject(Customer $customer)
    {
        $customerToFetch = (new Customer())->setId($customer->getId());
        $fetchedCustomer = $this->heidelpay->fetchCustomer($customerToFetch);
        $this->assertEquals($customer->getId(), $fetchedCustomer->getId());
    }

    /**
     * @param Customer $customer
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     * @depends maxCustomerCanBeCreatedAndFetched
     * @test
     */
    public function customerCanBeFetchedByObjectWithData(Customer $customer)
    {
        $customerToFetch = $this->getMinimalCustomer()->setId($customer->getId());
        $this->assertNotEquals($customer->getFirstname(), $customerToFetch->getFirstname());

        $fetchedCustomer = $this->heidelpay->fetchCustomer($customerToFetch);
        $this->assertEquals($customer->getFirstname(), $fetchedCustomer->getFirstname());
    }

    /**
     * Customer can be referenced by payment.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function transactionShouldCreateAndReferenceCustomerIfItDoesNotExistYet()
    {
        $customerId = 'c' . self::generateRandomId();
        $customer   = $this->getMaximumCustomerInclShippingAddress()->setCustomerId($customerId);

        /** @var Paypal $paypal */
        $paypal = $this->heidelpay->createPaymentType(new Paypal());
        $authorization = $paypal->authorize(12.0, 'EUR', self::RETURN_URL, $customer);

        /** @var Payment $secPayment */
        $secPayment = $this->heidelpay->fetchPayment($authorization->getPayment()->getId());

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
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function transactionShouldReferenceCustomerIfItExist()
    {
        $customer = $this->getMaximumCustomer();
        $this->heidelpay->createCustomer($customer);

        /** @var Paypal $paypal */
        $paypal = $this->heidelpay->createPaymentType(new Paypal());
        $authorization = $paypal->authorize(12.0, 'EUR', self::RETURN_URL, $customer);

        /** @var Payment $secPayment */
        $secPayment = $this->heidelpay->fetchPayment($authorization->getPayment()->getId());

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
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function transactionShouldReferenceCustomerIfItExistAndItsIdHasBeenPassed()
    {
        $customer = $this->getMaximumCustomer();
        $this->heidelpay->createCustomer($customer);

        /** @var Paypal $paypal */
        $paypal = $this->heidelpay->createPaymentType(new Paypal());
        $authorization = $paypal->authorize(12.0, 'EUR', self::RETURN_URL, $customer->getId());

        /** @var Payment $secPayment */
        $secPayment = $this->heidelpay->fetchPayment($authorization->getPayment()->getId());

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
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function customerShouldBeUpdatable(Customer $customer)
    {
        $this->assertEquals($customer->getFirstname(), 'Peter');
        $customer->setFirstname('Not Peter');
        $this->heidelpay->updateCustomer($customer);
        $this->assertEquals($customer->getFirstname(), 'Not Peter');

        /** @var Customer $fetchedCustomer */
        $fetchedCustomer = $this->heidelpay->fetchCustomer($customer->getId());
        $this->assertEquals($customer->getId(), $fetchedCustomer->getId());
        $this->assertEquals('Not Peter', $fetchedCustomer->getFirstname());
    }

    /**
     * Customer can be deleted.
     *
     * @depends maxCustomerCanBeCreatedAndFetched
     * @test
     *
     * @param Customer $customer
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function customerShouldBeDeletableById(Customer $customer)
    {
        $this->assertNotNull($customer);
        $this->assertNotNull($customer->getId());

        $this->heidelpay->deleteCustomer($customer->getId());

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_CUSTOMER_DOES_NOT_EXIST);
        $this->heidelpay->fetchCustomer($customer->getId());
    }

    /**
     * Customer can be deleted.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function customerShouldBeDeletableByObject()
    {
        $customer = $this->heidelpay->createCustomer($this->getMaximumCustomer());

        $fetchedCustomer = $this->heidelpay->fetchCustomer($customer->getId());
        $this->assertNotNull($customer);
        $this->assertNotNull($customer->getId());

        $this->heidelpay->deleteCustomer($customer);

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_CUSTOMER_DOES_NOT_EXIST);
        $this->heidelpay->fetchCustomer($fetchedCustomer->getId());
    }

    /**
     * Verify an Exception is thrown if the customerId already exists.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function apiShouldReturnErrorIfCustomerAlreadyExists()
    {
        $customerId = str_replace(' ', '', microtime());

        // create customer with api
        $customer = $this->heidelpay->createCustomer($this->getMaximumCustomer()->setCustomerId($customerId));
        $this->assertNotEmpty($customer->getCustomerId());

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_CUSTOMER_ID_ALREADY_EXISTS);

        // create new customer with the same customerId
        $this->heidelpay->createCustomer($this->getMaximumCustomer()->setCustomerId($customerId));
    }

    /**
     * Verify a Customer is fetched and updated when its customerId already exist.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function customerShouldBeFetchedByCustomerIdAndUpdatedIfItAlreadyExists()
    {
        $customerId = str_replace(' ', '', microtime());

        try {
            // fetch non-existing customer by customerId
            $this->heidelpay->fetchCustomerByExtCustomerId($customerId);
            $this->assertTrue(false, 'Exception should be thrown here.');
        } catch (HeidelpayApiException $e) {
            $this->assertEquals($e->getCode(), ApiResponseCodes::API_ERROR_CUSTOMER_CAN_NOT_BE_FOUND);
            $this->assertNotNull($e->getErrorId());
        }

        // create customer with api
        $customer = $this->heidelpay->createOrUpdateCustomer($this->getMaximumCustomer()->setCustomerId($customerId));
        $this->assertNotEmpty($customer->getCustomerId());
        $this->assertEquals($customerId, $customer->getCustomerId());
        $this->assertEquals('Peter', $customer->getFirstname());

        $newCustomerData = $this->getMaximumCustomer()->setCustomerId($customerId)->setFirstname('Petra');
        $this->heidelpay->createOrUpdateCustomer($newCustomerData);

        $this->assertEquals('Petra', $newCustomerData->getFirstname());
        $this->assertEquals($customerId, $newCustomerData->getCustomerId());
        $this->assertEquals($customer->getId(), $newCustomerData->getId());
    }

    /**
     * Verify customer address can take a name as long as both first and lastname concatenated.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function addressNameCanHoldFirstAndLastNameConcatenated()
    {
        $customerId = 'c' . self::generateRandomId();
        $customer   = $this->getMaximumCustomerInclShippingAddress()->setCustomerId($customerId);
        $longName   = 'firstfirstfirstfirstfirstfirstfirstfirst lastlastlastlastlastlastlastlastlastlast';
        $customer->getShippingAddress()->setName($longName);
        $this->heidelpay->createCustomer($customer);
        $this->assertEquals($longName, $customer->getShippingAddress()->getName());

        $veryLongName   = $longName . 'X';
        $customer->getShippingAddress()->setName($veryLongName);
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_ADDRESS_NAME_TO_LONG);
        $this->heidelpay->updateCustomer($customer);
    }

    //</editor-fold>

    //<editor-fold desc="not registered B2B Customer">

    /**
     * Not registered B2B customer should be creatable.
     *
     * @test
     *
     * @return Customer
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function minNotRegisteredB2bCustomerCanBeCreatedAndFetched(): Customer
    {
        /** @var Customer $customer */
        $customer = $this->getMinimalNotRegisteredB2bCustomer();
        $this->assertEmpty($customer->getId());
        $this->heidelpay->createCustomer($customer);
        $this->assertNotEmpty($customer->getId());

        /** @var Customer $fetchedCustomer */
        $fetchedCustomer = $this->heidelpay->fetchCustomer($customer->getId());
        $exposeArray     = $customer->expose();
        $exposeArray['salutation'] = Salutations::UNKNOWN;
        $this->assertEquals($exposeArray, $fetchedCustomer->expose());

        return $customer;
    }

    /**
     * Max not registered customer should be creatable.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function maxNotRegisteredB2bCustomerCanBeCreatedAndFetched()
    {
        /** @var Customer $customer */
        $customer = $this->getMaximalNotRegisteredB2bCustomer();
        $this->assertEmpty($customer->getId());
        $this->heidelpay->createCustomer($customer);
        $this->assertNotEmpty($customer->getId());

        /** @var Customer $fetchedCustomer */
        $fetchedCustomer = $this->heidelpay->fetchCustomer($customer->getId());
        $this->assertEquals($customer->expose(), $fetchedCustomer->expose());
    }

    //</editor-fold>

    //<editor-fold desc="registered B2B Customer">

    /**
     * Registered B2B customer should be creatable.
     *
     * @test
     *
     * @return Customer
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function minRegisteredB2bCustomerCanBeCreatedAndFetched(): Customer
    {
        /** @var Customer $customer */
        $customer = $this->getMinimalRegisteredB2bCustomer();
        $this->assertEmpty($customer->getId());
        $this->heidelpay->createCustomer($customer);
        $this->assertNotEmpty($customer->getId());

        /** @var Customer $fetchedCustomer */
        $fetchedCustomer = $this->heidelpay->fetchCustomer($customer->getId());
        $exposeArray     = $customer->expose();
        $exposeArray['salutation'] = Salutations::UNKNOWN;
        $this->assertEquals($exposeArray, $fetchedCustomer->expose());

        return $customer;
    }

    /**
     * Max registered customer should be creatable.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function maxRegisteredB2bCustomerCanBeCreatedAndFetched()
    {
        /** @var Customer $customer */
        $customer = $this->getMaximalRegisteredB2bCustomer();
        $this->assertEmpty($customer->getId());
        $this->heidelpay->createCustomer($customer);
        $this->assertNotEmpty($customer->getId());

        /** @var Customer $fetchedCustomer */
        $fetchedCustomer = $this->heidelpay->fetchCustomer($customer->getId());
        $this->assertEquals($customer->expose(), $fetchedCustomer->expose());
    }

    //</editor-fold>
}
