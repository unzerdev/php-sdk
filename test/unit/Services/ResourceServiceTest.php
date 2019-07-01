<?php
/**
 * This class defines unit tests to verify functionality of the resource service.
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/test/unit
 */
namespace heidelpayPHP\test\unit\Services;

use DateTime;
use Exception;
use heidelpayPHP\Adapter\HttpAdapterInterface;
use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\AbstractHeidelpayResource;
use heidelpayPHP\Resources\Basket;
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\Keypair;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\Alipay;
use heidelpayPHP\Resources\PaymentTypes\BasePaymentType;
use heidelpayPHP\Resources\PaymentTypes\Card;
use heidelpayPHP\Resources\PaymentTypes\EPS;
use heidelpayPHP\Resources\PaymentTypes\Giropay;
use heidelpayPHP\Resources\PaymentTypes\Ideal;
use heidelpayPHP\Resources\PaymentTypes\Invoice;
use heidelpayPHP\Resources\PaymentTypes\InvoiceFactoring;
use heidelpayPHP\Resources\PaymentTypes\InvoiceGuaranteed;
use heidelpayPHP\Resources\PaymentTypes\Paypal;
use heidelpayPHP\Resources\PaymentTypes\PIS;
use heidelpayPHP\Resources\PaymentTypes\Prepayment;
use heidelpayPHP\Resources\PaymentTypes\Przelewy24;
use heidelpayPHP\Resources\PaymentTypes\SepaDirectDebit;
use heidelpayPHP\Resources\PaymentTypes\SepaDirectDebitGuaranteed;
use heidelpayPHP\Resources\PaymentTypes\Sofort;
use heidelpayPHP\Resources\PaymentTypes\Wechatpay;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\Resources\TransactionTypes\Shipment;
use heidelpayPHP\Services\HttpService;
use heidelpayPHP\Services\IdService;
use heidelpayPHP\Services\ResourceService;
use heidelpayPHP\test\BaseUnitTest;
use heidelpayPHP\test\unit\DummyResource;
use ReflectionException;
use RuntimeException;
use stdClass;

class ResourceServiceTest extends BaseUnitTest
{
    /**
     * Verify getResourceIdFromUrl works correctly.
     *
     * @test
     * @dataProvider urlIdStringProvider
     *
     * @param string $expected
     * @param string $uri
     * @param string $idString
     *
     * @throws RuntimeException
     */
    public function getResourceIdFromUrlShouldIdentifyAndReturnTheIdStringFromAGivenString($expected, $uri, $idString)
    {
        $this->assertEquals($expected, IdService::getResourceIdFromUrl($uri, $idString));
    }

    /**
     * Verify getResourceIdFromUrl throws exception if the id cannot be found.
     *
     * @test
     * @dataProvider failingUrlIdStringProvider
     *
     * @throws RuntimeException
     *
     * @param mixed $uri
     * @param mixed $idString
     */
    public function getResourceIdFromUrlShouldThrowExceptionIfTheIdCanNotBeFound($uri, $idString)
    {
        $this->expectException(RuntimeException::class);
        IdService::getResourceIdFromUrl($uri, $idString);
    }

    /**
     * Verify getResource calls fetch if its id is set and it has never been fetched before.
     *
     * @test
     * @dataProvider getResourceFetchCallDataProvider
     *
     * @param $resource
     * @param $timesFetchIsCalled
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function getResourceShouldFetchIfTheResourcesIdIsSetAndItHasNotBeenFetchedBefore(
        $resource,
        $timesFetchIsCalled
    ) {
        $resourceSrv = $this->getMockBuilder(ResourceService::class)->setMethods(['fetch'])
            ->disableOriginalConstructor()->getMock();
        $resourceSrv->expects($this->exactly($timesFetchIsCalled))->method('fetch')->with($resource);

        /** @var ResourceService $resourceSrv */
        $resourceSrv->getResource($resource);
    }

    /**
     * Verify create method will call send method and call the resources handleResponse method with the response.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function createShouldCallSendAndThenHandleResponseWithTheResponseData()
    {
        $response = new stdClass();
        $response->id = 'myTestId';

        $testResource = $this->getMockBuilder(Customer::class)->setMethods(['handleResponse'])->getMock();
        $testResource->expects($this->once())->method('handleResponse')
            ->with($response, HttpAdapterInterface::REQUEST_POST);

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)->setMethods(['send'])
            ->disableOriginalConstructor()->getMock();
        $resourceServiceMock->expects($this->once())->method('send')
            ->with($testResource, HttpAdapterInterface::REQUEST_POST)->willReturn($response);

        /**
         * @var ResourceService           $resourceServiceMock
         * @var AbstractHeidelpayResource $testResource
         */
        $this->assertSame($testResource, $resourceServiceMock->create($testResource));
        $this->assertEquals('myTestId', $testResource->getId());
    }

    /**
     * Verify create does not handle response with error.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function createShouldNotHandleResponseWithError()
    {
        $response = new stdClass();
        $response->isError = true;
        $response->id = 'myId';

        $testResource = $this->getMockBuilder(Customer::class)->setMethods(['handleResponse'])->getMock();
        $testResource->expects($this->never())->method('handleResponse');

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)->setMethods(['send'])
            ->disableOriginalConstructor()->getMock();
        $resourceServiceMock->expects($this->once())->method('send')
            ->with($testResource, HttpAdapterInterface::REQUEST_POST)->willReturn($response);

        /**
         * @var ResourceService           $resourceServiceMock
         * @var AbstractHeidelpayResource $testResource
         */
        $this->assertSame($testResource, $resourceServiceMock->create($testResource));
        $this->assertNull($testResource->getId());
    }

    /**
     * Verify update method will call send method and call the resources handleResponse method with the response.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function updateShouldCallSendAndThenHandleResponseWithTheResponseData()
    {
        $response = new stdClass();

        $testResource = $this->getMockBuilder(Customer::class)->setMethods(['handleResponse'])->getMock();
        $testResource->expects($this->once())->method('handleResponse')
            ->with($response, HttpAdapterInterface::REQUEST_PUT);

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)->setMethods(['send'])
            ->disableOriginalConstructor()->getMock();
        $resourceServiceMock->expects($this->once())->method('send')
            ->with($testResource, HttpAdapterInterface::REQUEST_PUT)->willReturn($response);

        /**
         * @var ResourceService           $resourceServiceMock
         * @var AbstractHeidelpayResource $testResource
         */
        $this->assertSame($testResource, $resourceServiceMock->update($testResource));
    }

    /**
     * Verify update does not handle response with error.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function updateShouldNotHandleResponseWithError()
    {
        $response = new stdClass();
        $response->isError = true;

        $testResource = $this->getMockBuilder(Customer::class)->setMethods(['handleResponse'])->getMock();
        $testResource->expects($this->never())->method('handleResponse');

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)->setMethods(['send'])
            ->disableOriginalConstructor()->getMock();
        $resourceServiceMock->expects($this->once())->method('send')
            ->with($testResource, HttpAdapterInterface::REQUEST_PUT)->willReturn($response);

        /**
         * @var ResourceService           $resourceServiceMock
         * @var AbstractHeidelpayResource $testResource
         */
        $this->assertSame($testResource, $resourceServiceMock->update($testResource));
    }

    /**
     * Verify delete method will call send method and set resource null if successful.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function deleteShouldCallSendAndThenSetTheResourceNull()
    {
        $response = new stdClass();

        $testResource = $this->getMockBuilder(Customer::class)->getMock();
        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)->setMethods(['send'])
            ->disableOriginalConstructor()->getMock();
        $resourceServiceMock->expects($this->once())->method('send')
            ->with($testResource, HttpAdapterInterface::REQUEST_DELETE)->willReturn($response);

        /**
         * @var ResourceService           $resourceServiceMock
         * @var AbstractHeidelpayResource $testResource
         */
        $this->assertNull($resourceServiceMock->delete($testResource));
        $this->assertNull($testResource);
    }

    /**
     * Verify delete does not delete resource object on error response.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function deleteShouldNotDeleteObjectOnResponseWithError()
    {
        $response = new stdClass();
        $response->isError = true;

        $testResource = $this->getMockBuilder(Customer::class)->getMock();

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)->setMethods(['send'])
            ->disableOriginalConstructor()->getMock();
        $resourceServiceMock->expects($this->once())->method('send')
            ->with($testResource, HttpAdapterInterface::REQUEST_DELETE)->willReturn($response);

        /**
         * @var ResourceService           $resourceServiceMock
         * @var AbstractHeidelpayResource $testResource
         */
        $responseResource = $resourceServiceMock->delete($testResource);
        $this->assertNotNull($responseResource);
        $this->assertNotNull($testResource);
        $this->assertSame($testResource, $responseResource);
    }

    /**
     * Verify fetch method will call send with GET the resource and then call handleResponse.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws Exception
     */
    public function fetchShouldCallSendWithGetUpdateFetchedAtAndCallHandleResponse()
    {
        $response = new stdClass();
        $response->test = '234';
        $resourceMock = $this->getMockBuilder(Customer::class)->setMethods(['handleResponse'])->getMock();
        $resourceMock->expects($this->once())->method('handleResponse')->with($response);

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['send'])
            ->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->once())->method('send')
            ->with($resourceMock, HttpAdapterInterface::REQUEST_GET)
            ->willReturn($response);

        /**
         * @var AbstractHeidelpayResource $resourceMock
         * @var ResourceService           $resourceSrvMock
         */
        $this->assertNull($resourceMock->getFetchedAt());
        $resourceSrvMock->fetch($resourceMock);

        $now = (new DateTime('now'))->getTimestamp();
        $then = $resourceMock->getFetchedAt()->getTimestamp();
        $this->assertTrue(($now - $then) < 60);
    }

    /**
     * Verify fetchPayment method will fetch the passed payment object.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchPaymentShouldCallFetchWithTheGivenPaymentObject()
    {
        $payment = (new Payment())->setId('myPaymentId');

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetch'])
            ->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->once())->method('fetch')->with($payment);

        /** @var ResourceService $resourceSrvMock */
        $returnedPayment = $resourceSrvMock->fetchPayment($payment);
        $this->assertSame($payment, $returnedPayment);
    }

    /**
     * Verify fetchPayment method called with paymentId will create a payment object set its id and call fetch with it.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchPaymentCalledWithIdShouldCreatePaymentObjectWithIdAndCallFetch()
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetch'])
            ->setConstructorArgs([$heidelpay])->getMock();
        $resourceSrvMock->expects($this->once())->method('fetch')
            ->with($this->callback(
                static function ($payment) use ($heidelpay) {
                    return $payment instanceof Payment &&
                    $payment->getId() === 'testPaymentId' &&
                    $payment->getHeidelpayObject() === $heidelpay;
                }));

        /** @var ResourceService $resourceSrvMock */
        $resourceSrvMock->fetchPayment('testPaymentId');
    }

    /**
     * Verify fetchPaymentByOrderId method will create a payment object set its orderId and call fetch with it.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchPaymentByOrderIdShouldCreatePaymentObjectWithOrderIdAndCallFetch()
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetch'])->setConstructorArgs([$heidelpay])->getMock();
        $resourceSrvMock->expects($this->once())->method('fetch')
            ->with($this->callback(
                static function ($payment) use ($heidelpay) {
                    return $payment instanceof Payment &&
                    $payment->getOrderId() === 'myOrderId' &&
                    $payment->getId() === 'myOrderId' &&
                    $payment->getHeidelpayObject() === $heidelpay;
                }));

        /** @var ResourceService $resourceSrvMock */
        $resourceSrvMock->fetchPaymentByOrderId('myOrderId');
    }

    /**
     * Verify fetchKeypair will call fetch with a Keypair object.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchKeypairShouldCallFetchWithAKeypairObject()
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetch'])
            ->setConstructorArgs([$heidelpay])->getMock();
        $resourceSrvMock->expects($this->once())->method('fetch')
            ->with($this->callback(
                static function ($keypair) use ($heidelpay) {
                    return $keypair instanceof Keypair && $keypair->getHeidelpayObject() === $heidelpay;
                }));

        /** @var ResourceService $resourceSrvMock */
        $resourceSrvMock->fetchKeypair();
    }

    /**
     * Verify createPaymentType method will set parentResource to heidelpay object and call create.
     *
     * @test
     *
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function createPaymentTypeShouldSetHeidelpayObjectAndCallCreate()
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $paymentType = new Sofort();

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['create'])
            ->setConstructorArgs([$heidelpay])->getMock();
        $resourceSrvMock->expects($this->once())->method('create')
            ->with($this->callback(
                static function ($type) use ($heidelpay, $paymentType) {
                    return $type === $paymentType && $type->getHeidelpayObject() === $heidelpay;
                }));

        /** @var ResourceService $resourceSrvMock */
        $returnedType = $resourceSrvMock->createPaymentType($paymentType);

        $this->assertSame($paymentType, $returnedType);
    }

    /**
     * Verify fetchPaymentType method is creating the correct payment type instance depending on the passed id.
     *
     * @test
     * @dataProvider paymentTypeAndIdProvider
     *
     * @param string $typeClass
     * @param string $typeId
     *
     * @throws RuntimeException
     * @throws ReflectionException
     * @throws HeidelpayApiException
     */
    public function fetchPaymentTypeShouldFetchCorrectPaymentInstanceDependingOnId($typeClass, $typeId)
    {
        $heidelpay = new Heidelpay('s-priv-1234');

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetch'])
            ->setConstructorArgs([$heidelpay])->getMock();
        $resourceSrvMock->expects($this->once())->method('fetch')
            ->with($this->callback(
                static function ($type) use ($heidelpay, $typeClass, $typeId) {
                    /** @var BasePaymentType $type */
                    return $type instanceof $typeClass &&
                    $type->getHeidelpayObject() === $heidelpay &&
                    $type->getId() === $typeId;
                }));

        /** @var ResourceService $resourceSrvMock */
        $resourceSrvMock->fetchPaymentType($typeId);
    }

    /**
     * Verify fetchPaymentType will throw exception if the id does not fit any type or is invalid.
     *
     * @test
     * @dataProvider paymentTypeIdProviderInvalid
     *
     * @param string $typeId
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchPaymentTypeShouldThrowExceptionOnInvalidTypeId($typeId)
    {
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetch'])
            ->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->never())->method('fetch');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid payment type!');

        /** @var ResourceService $resourceSrvMock */
        $resourceSrvMock->fetchPaymentType($typeId);
    }

    /**
     * Verify createCustomer calls create with customer object and the heidelpay resource is set.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function createCustomerShouldCallCreateWithCustomerObjectAndSetHeidelpayReference()
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $customer = new Customer();

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['create'])
            ->setConstructorArgs([$heidelpay])->getMock();
        $resourceSrvMock->expects($this->once())->method('create')
            ->with($this->callback(
                static function ($resource) use ($heidelpay, $customer) {
                    return $resource === $customer && $resource->getHeidelpayObject() === $heidelpay;
                }));

        /** @var ResourceService $resourceSrvMock */
        $returnedCustomer = $resourceSrvMock->createCustomer($customer);

        $this->assertSame($customer, $returnedCustomer);
    }

    /**
     * Verify createOrUpdateCustomer method tries to fetch and update the given customer.
     *
     * @test
     *
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function createOrUpdateCustomerShouldFetchAndUpdateCustomerIfItAlreadyExists()
    {
        $customer = (new Customer())->setCustomerId('externalCustomerId')->setEmail('customer@email.de');
        $fetchedCustomer = (new Customer('Max', 'Mustermann'))
            ->setCustomerId('externalCustomerId')
            ->setId('customerId');

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()
            ->setMethods(['createCustomer', 'fetchCustomer', 'updateCustomer'])->getMock();

        $resourceSrvMock->expects($this->once())->method('createCustomer')->with($customer)->willThrowException(
            new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_CUSTOMER_ID_ALREADY_EXISTS)
        );
        $resourceSrvMock->expects($this->once())->method('fetchCustomer')
            ->with($this->callback(
                static function ($customerToFetch) use ($customer) {
                    /** @var Customer $customerToFetch */
                    return $customerToFetch !== $customer &&
                       $customerToFetch->getId() === $customer->getId() &&
                       $customerToFetch->getCustomerId() === $customer->getCustomerId();
                }))->willReturn($fetchedCustomer);
        $resourceSrvMock->expects($this->once())->method('updateCustomer')
            ->with($this->callback(
                static function ($customerToUpdate) use ($customer) {
                    /** @var Customer $customerToUpdate */
                    return $customerToUpdate === $customer &&
                       $customerToUpdate->getId() === $customer->getId() &&
                       $customerToUpdate->getEmail() === 'customer@email.de';
                }));

        /** @var ResourceService $resourceSrvMock */
        $returnedCustomer = $resourceSrvMock->createOrUpdateCustomer($customer);
        $this->assertSame($customer, $returnedCustomer);
        $this->assertEquals('customerId', $customer->getId());
        $this->assertEquals('customer@email.de', $customer->getEmail());
    }

    /**
     * Verify createOrUpdateCustomer method does not call fetch or update if a the customer could not be created due
     * to another reason then id already exists.
     *
     * @test
     *
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function createOrUpdateCustomerShouldThrowTheExceptionIfItIsNotCustomerIdAlreadyExists()
    {
        $customer = (new Customer())->setCustomerId('externalCustomerId')->setEmail('customer@email.de');

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()
            ->setMethods(['createCustomer', 'fetchCustomer', 'updateCustomer'])->getMock();

        $exc = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_CUSTOMER_ID_REQUIRED);
        $resourceSrvMock->expects($this->once())->method('createCustomer')->with($customer)->willThrowException($exc);
        $resourceSrvMock->expects($this->never())->method('fetchCustomer');
        $resourceSrvMock->expects($this->never())->method('updateCustomer');

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_CUSTOMER_ID_REQUIRED);

        /** @var ResourceService $resourceSrvMock */
        $resourceSrvMock->createOrUpdateCustomer($customer);
    }

    /**
     * Verify fetchCustomer method calls fetch with the customer object provided.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchCustomerShouldCallFetchWithTheGivenCustomerAndSetHeidelpayReference()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $customer = (new Customer())->setId('myCustomerId');

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetch'])
            ->setConstructorArgs([$heidelpay])->getMock();
        $resourceSrvMock->expects($this->once())->method('fetch')->with($customer);

        try {
            $customer->getHeidelpayObject();
            $this->assertTrue(false, 'This exception should have been thrown!');
        } catch (RuntimeException $e) {
            $this->assertInstanceOf(RuntimeException::class, $e);
            $this->assertEquals('Parent resource reference is not set!', $e->getMessage());
        }

        /** @var ResourceService $resourceSrvMock */
        $returnedCustomer = $resourceSrvMock->fetchCustomer($customer);
        $this->assertSame($customer, $returnedCustomer);
        $this->assertSame($heidelpay, $customer->getHeidelpayObject());
    }

    /**
     * Verify fetchCustomer will call fetch with a new Customer object if the customer is referenced by id.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchCustomerShouldCallFetchWithNewCustomerObject()
    {
        $heidelpay = new Heidelpay('s-priv-123');

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetch'])
            ->setConstructorArgs([$heidelpay])->getMock();
        $resourceSrvMock->expects($this->once())->method('fetch')->with(
            $this->callback(
                static function ($param) use ($heidelpay) {
                    return $param instanceof Customer &&
                       $param->getId() === 'myCustomerId' &&
                       $param->getHeidelpayObject() === $heidelpay;
                })
        );

        /** @var ResourceService $resourceSrvMock */
        $returnedCustomer = $resourceSrvMock->fetchCustomer('myCustomerId');
        $this->assertEquals('myCustomerId', $returnedCustomer->getId());
        $this->assertEquals($heidelpay, $returnedCustomer->getHeidelpayObject());
    }

    /**
     * Verify updateCustomer calls update with customer object.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function updateCustomerShouldCallUpdateWithCustomerObject()
    {
        $customer = (new Customer())->setId('customerId');

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['update'])
            ->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->once())->method('update')->with($customer);

        /** @var ResourceService $resourceSrvMock */
        $returnedCustomer = $resourceSrvMock->updateCustomer($customer);

        $this->assertSame($customer, $returnedCustomer);
    }

    /**
     * Verify deleteCustomer method calls delete with customer object.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function deleteCustomerShouldCallDeleteWithTheGivenCustomer()
    {
        $customer = (new Customer())->setId('customerId');

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['delete'])
            ->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->once())->method('delete')->with($customer);

        /** @var ResourceService $resourceSrvMock */
        $returnedCustomer = $resourceSrvMock->deleteCustomer($customer);
        $this->assertSame($customer, $returnedCustomer);
    }

    /**
     * Verify deleteCustomer calls fetchCustomer with id if the customer object is referenced by id.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function deleteCustomerShouldFetchCustomerByIdIfTheIdIsGiven()
    {
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['delete', 'fetchCustomer'])
            ->disableOriginalConstructor()->getMock();
        $customer       = new Customer('Max', 'Mustermann');
        $resourceSrvMock->expects($this->once())->method('fetchCustomer')->with('myCustomerId')->willReturn($customer);
        $resourceSrvMock->expects($this->once())->method('delete')->with($customer);

        /** @var ResourceService $resourceSrvMock */
        $returnedCustomer = $resourceSrvMock->deleteCustomer('myCustomerId');
        $this->assertSame($customer, $returnedCustomer);
    }

    /**
     * Verify fetchAuthorization fetches payment object and returns its authorization.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchAuthorizationShouldFetchPaymentAndReturnItsAuthorization()
    {
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchPayment', 'fetch'])
            ->disableOriginalConstructor()->getMock();
        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getAuthorization'])->getMock();

        $authorize = (new Authorization())->setId('s-aut-1');
        $resourceSrvMock->expects($this->once())->method('fetchPayment')->with($paymentMock)->willReturn($paymentMock);
        $paymentMock->expects($this->once())->method('getAuthorization')->willReturn($authorize);
        $resourceSrvMock->expects($this->once())->method('fetch')->with($authorize)->willReturn($authorize);

        /** @var ResourceService $resourceSrvMock */
        $returnedAuthorize = $resourceSrvMock->fetchAuthorization($paymentMock);
        $this->assertSame($authorize, $returnedAuthorize);
    }

    /**
     * Verify fetchChargeById fetches payment object and gets and returns the charge object from it.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchChargeByIdShouldFetchPaymentAndReturnTheChargeOfThePayment()
    {
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchPayment', 'fetch'])
            ->disableOriginalConstructor()->getMock();
        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getCharge'])->getMock();

        $charge = (new Charge())->setId('chargeId');
        $paymentMock->expects($this->once())->method('getCharge')->with('chargeId')->willReturn($charge);
        $resourceSrvMock->expects($this->once())->method('fetchPayment')->with($paymentMock)->willReturn($paymentMock);
        $resourceSrvMock->expects($this->once())->method('fetch')->with($charge)->willReturn($charge);

        /** @var ResourceService $resourceSrvMock */
        $returnedCharge = $resourceSrvMock->fetchChargeById($paymentMock, 'chargeId');
        $this->assertSame($charge, $returnedCharge);
    }

    /**
     * Verify fetchReversalByAuthorization fetches authorization and gets and returns the reversal object from it.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchReversalByAuthorizationShouldFetchAuthorizeAndReturnTheReversalFromIt()
    {
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetch'])
            ->disableOriginalConstructor()->getMock();
        $authorizeMock = $this->getMockBuilder(Authorization::class)->setMethods(['getCancellation'])->getMock();

        $cancellation = new Cancellation();
        $resourceSrvMock->expects($this->once())->method('fetch')->with($authorizeMock);

        $authorizeMock->expects($this->once())->method('getCancellation')->with('cancelId')->willReturn($cancellation);

        /** @var ResourceService $resourceSrvMock */
        $returnedCancel = $resourceSrvMock->fetchReversalByAuthorization($authorizeMock, 'cancelId');
        $this->assertSame($cancellation, $returnedCancel);
    }

    /**
     * Verify fetchReversal will fetch payment by id and get and return the desired reversal from it.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchReversalShouldFetchPaymentAndReturnDesiredReversalFromIt()
    {
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchPayment'])
            ->disableOriginalConstructor()->getMock();
        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getAuthorization'])->getMock();
        $authorizationMock = $this->getMockBuilder(Authorization::class)->setMethods(['getCancellation'])->getMock();

        $cancel = (new Cancellation())->setId('cancelId');
        $resourceSrvMock->expects($this->once())->method('fetchPayment')->willReturn($paymentMock);
        $paymentMock->expects($this->once())->method('getAuthorization')->willReturn($authorizationMock);
        $authorizationMock->expects($this->once())->method('getCancellation')->willReturn($cancel);

        /** @var ResourceService $resourceSrvMock */
        $returnedCancel = $resourceSrvMock->fetchReversal('paymentId', 'cancelId');
        $this->assertSame($cancel, $returnedCancel);
    }

    /**
     * Verify fetchRefundById fetches charge object by id and fetches desired refund from it.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchRefundByIdShouldFetchChargeByIdAndThenFetchTheDesiredRefundFromIt()
    {
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchChargeById', 'fetchRefund'])
            ->disableOriginalConstructor()->getMock();

        $charge = (new Charge())->setId('chargeId');
        $cancel = (new Cancellation())->setId('cancellationId');
        $resourceSrvMock->expects($this->once())->method('fetchChargeById')->with('paymentId', 'chargeId')
            ->willReturn($charge);
        $resourceSrvMock->expects($this->once())->method('fetchRefund')->with($charge, 'cancellationId')
            ->willReturn($cancel);

        /** @var ResourceService $resourceSrvMock */
        $returnedCancellation = $resourceSrvMock->fetchRefundById('paymentId', 'chargeId', 'cancellationId');
        $this->assertSame($cancel, $returnedCancellation);
    }

    /**
     * Verify fetchRefund gets and fetches desired charge cancellation.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchRefundShouldGetAndFetchDesiredChargeCancellation()
    {
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetch'])
            ->disableOriginalConstructor()->getMock();
        $chargeMock = $this->getMockBuilder(Charge::class)->setMethods(['getCancellation'])->getMock();

        $cancel = (new Cancellation())->setId('cancellationId');
        $chargeMock->expects($this->once())->method('getCancellation')->with('cancellationId', true)
            ->willReturn($cancel);
        $resourceSrvMock->expects($this->once())->method('fetch')->with($cancel)->willReturn($cancel);

        /**
         * @var ResourceService $resourceSrvMock
         * @var Charge          $chargeMock
         */
        $returnedCancellation = $resourceSrvMock->fetchRefund($chargeMock, 'cancellationId');
        $this->assertSame($cancel, $returnedCancellation);
    }

    /**
     * Verify fetchShipment fetches payment object and returns the desired shipment from it.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchShipmentShouldFetchPaymentAndReturnTheDesiredShipmentFromIt()
    {
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchPayment'])
            ->disableOriginalConstructor()->getMock();
        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getShipment'])->getMock();

        $shipment = (new Shipment())->setId('shipmentId');
        $resourceSrvMock->expects($this->once())->method('fetchPayment')->with('paymentId')->willReturn($paymentMock);
        $paymentMock->expects($this->once())->method('getShipment')->with('shipmentId', false)->willReturn($shipment);

        /**
         * @var ResourceService $resourceSrvMock
         * @var Payment         $paymentMock
         */
        $returnedShipment = $resourceSrvMock->fetchShipment('paymentId', 'shipmentId');
        $this->assertSame($shipment, $returnedShipment);
    }

    /**
     * Verify fetchMetadata calls fetch with the given metadata object.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchMetadataShouldCallFetchWithTheGivenMetadataObject()
    {
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetch'])
            ->disableOriginalConstructor()->getMock();

        $metadata = (new Metadata())->setId('myMetadataId');
        $resourceSrvMock->expects($this->once())->method('fetch')->with($metadata);

        /** @var ResourceService $resourceSrvMock */
        $this->assertSame($metadata, $resourceSrvMock->fetchMetadata($metadata));
    }

    /**
     * Verify createMetadata calls create with the given metadata object.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function createMetadataShouldCallCreateWithTheGivenMetadataObject()
    {
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();

        $metadata = new Metadata();
        $resourceSrvMock->expects($this->once())->method('create')->with($metadata);

        /** @var ResourceService $resourceSrvMock */
        $this->assertSame($metadata, $resourceSrvMock->createMetadata($metadata));
    }

    /**
     * Verify fetchMetadata calls fetch with a new metadata object with the given id.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchMetadataShouldCallFetchWithANewMetadataObjectWithTheGivenId()
    {
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetch'])
            ->disableOriginalConstructor()->getMock();

        $resourceSrvMock->expects($this->once())->method('fetch')->with(
            $this->callback(
                static function ($metadata) {
                    return $metadata instanceof Metadata && $metadata->getId() === 's-mtd-1234';
                })
        );

        /** @var ResourceService $resourceSrvMock */
        $resourceSrvMock->fetchMetadata('s-mtd-1234');
    }

    /**
     * Verify send will call send on httpService.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws ReflectionException
     * @throws HeidelpayApiException
     */
    public function sendShouldCallSendOnHttpService()
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $resourceMock = $this->getMockBuilder(DummyResource::class)->setMethods(
            ['getUri', 'getHeidelpayObject']
        )->getMock();
        $resourceMock->expects($this->exactly(4))->method('getUri')
            ->withConsecutive([true], [false], [true], [true])
            ->willReturnOnConsecutiveCalls('/my/get/uri', '/my/post/uri', '/my/put/uri', '/my/delete/uri');
        $resourceMock->method('getHeidelpayObject')->willReturn($heidelpay);
        $httpSrvMock = $this->getMockBuilder(HttpService::class)->setMethods(['send'])->getMock();
        $resourceSrv = new ResourceService($heidelpay);

        /** @var HttpService $httpSrvMock */
        $heidelpay->setHttpService($httpSrvMock);
        $httpSrvMock->expects($this->exactly(4))->method('send')->withConsecutive(
            ['/my/get/uri', $resourceMock, 'GET'],
            ['/my/post/uri', $resourceMock, 'POST'],
            ['/my/put/uri', $resourceMock, 'PUT'],
            ['/my/delete/uri', $resourceMock, 'DELETE']
        )->willReturn('{"response": "This is the response"}');

        /** @var AbstractHeidelpayResource $resourceMock */
        $response = $resourceSrv->send($resourceMock);
        $this->assertEquals('This is the response', $response->response);

        $response = $resourceSrv->send($resourceMock, HttpAdapterInterface::REQUEST_POST);
        $this->assertEquals('This is the response', $response->response);

        $response = $resourceSrv->send($resourceMock, HttpAdapterInterface::REQUEST_PUT);
        $this->assertEquals('This is the response', $response->response);

        $response = $resourceSrv->send($resourceMock, HttpAdapterInterface::REQUEST_DELETE);
        $this->assertEquals('This is the response', $response->response);
    }

    /**
     * Verify createBasket will set parentResource and call create with the given basket.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws ReflectionException
     * @throws HeidelpayApiException
     */
    public function createBasketShouldSetTheParentResourceAndCallCreateWithTheGivenBasket()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)
            ->setConstructorArgs([$heidelpay])
            ->setMethods(['create'])->getMock();
        $resourceSrvMock->expects($this->once())->method('create');

        $basket = new Basket();
        try {
            $basket->getParentResource();
            $this->assertTrue(false, 'This exception should have been thrown!');
        } catch (RuntimeException $e) {
            $this->assertEquals('Parent resource reference is not set!', $e->getMessage());
        }

        /** @var ResourceService $resourceSrvMock */
        $this->assertSame($basket, $resourceSrvMock->createBasket($basket));
        $this->assertSame($heidelpay, $basket->getParentResource());
    }

    /**
     * Verify fetchBasket will create basket obj and call fetch with it if the id is given.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws ReflectionException
     * @throws HeidelpayApiException
     */
    public function fetchBasketShouldCreateBasketObjectWithGivenIdAndCallFetchWithIt()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)
            ->setConstructorArgs([$heidelpay])
            ->setMethods(['fetch'])->getMock();
        $resourceSrvMock->expects($this->once())->method('fetch')->with(
            $this->callback(
                static function ($basket) use ($heidelpay) {
                    /** @var Basket $basket */
                    return $basket->getId() === 'myBasketId' && $basket->getParentResource() === $heidelpay;
                })
        );

        /** @var ResourceService $resourceSrvMock */
        $basket = $resourceSrvMock->fetchBasket('myBasketId');

        $this->assertEquals('myBasketId', $basket->getId());
        $this->assertEquals($heidelpay, $basket->getParentResource());
        $this->assertEquals($heidelpay, $basket->getHeidelpayObject());
    }

    /**
     * Verify fetchBasket will call fetch with the given basket obj.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws ReflectionException
     * @throws HeidelpayApiException
     */
    public function fetchBasketShouldCallFetchWithTheGivenBasketObject()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)
            ->setConstructorArgs([$heidelpay])
            ->setMethods(['fetch'])->getMock();

        $basket = new Basket();
        $resourceSrvMock->expects($this->once())->method('fetch')->with($basket);

        /** @var ResourceService $resourceSrvMock */
        $returnedBasket = $resourceSrvMock->fetchBasket($basket);

        $this->assertSame($basket, $returnedBasket);
        $this->assertEquals($heidelpay, $basket->getParentResource());
        $this->assertEquals($heidelpay, $basket->getHeidelpayObject());
    }

    /**
     * Verify updateBasket calls update with the given basket and returns it.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function updateBasketShouldCallUpdateAndReturnTheGivenBasket()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)
            ->setConstructorArgs([$heidelpay])->setMethods(['update'])->getMock();

        $basket = new Basket();
        $resourceSrvMock->expects($this->once())->method('update')->with($basket);

        /** @var ResourceService $resourceSrvMock */
        $returnedBasket = $resourceSrvMock->updateBasket($basket);

        $this->assertSame($basket, $returnedBasket);
        $this->assertEquals($heidelpay, $basket->getParentResource());
        $this->assertEquals($heidelpay, $basket->getHeidelpayObject());
    }

    /**
     * Verify fetchResourceByUrl calls fetch for the desired resource.
     *
     * @test
     * @dataProvider fetchResourceByUrlShouldFetchTheDesiredResourceDP
     *
     * @param string $expectedFetchMethod
     * @param mixed  $expectedArguments
     * @param string $resourceUrl
     *
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchResourceByUrlShouldFetchTheDesiredResource(
        $expectedFetchMethod,
        $expectedArguments,
        $resourceUrl
    ) {
        $heidelpayMock = $this->getMockBuilder(Heidelpay::class)->disableOriginalConstructor()->setMethods(
            [$expectedFetchMethod]
        )->getMock();
        $heidelpayMock->expects($this->once())->method($expectedFetchMethod)->with(...$expectedArguments);

        /** @var Heidelpay $heidelpayMock */
        $resourceService = new ResourceService($heidelpayMock);

        $resourceService->fetchResourceByUrl($resourceUrl);
    }

    /**
     * Verify fetchResourceByUrl calls fetch for the desired resource.
     *
     * @test
     * @dataProvider fetchResourceByUrlForAPaymentTypeShouldCallFetchPaymentTypeDP
     *
     * @param $paymentTypeId
     * @param string $resourceUrl
     *
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchResourceByUrlForAPaymentTypeShouldCallFetchPaymentType($paymentTypeId, $resourceUrl)
    {
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()
            ->setMethods(['fetchPaymentType'])->getMock();

        $resourceSrvMock->expects($this->once())->method('fetchPaymentType')->with($paymentTypeId);

        /** @var ResourceService $resourceSrvMock */
        $resourceSrvMock->fetchResourceByUrl($resourceUrl);
    }

    /**
     * Verify does not call fetchResourceByUrl and returns null if the resource type is unknown.
     *
     * @test
     *
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchResourceByUrlForAPaymentTypeShouldReturnNullIfTheTypeIsUnknown()
    {
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()
            ->setMethods(['fetchPaymentType'])->getMock();

        $resourceSrvMock->expects($this->never())->method('fetchPaymentType');

        /** @var ResourceService $resourceSrvMock */
        $this->assertNull(
            $resourceSrvMock->fetchResourceByUrl('https://api.heidelpay.com/v1/types/card/s-unknown-xen2ybcovn56/')
        );
    }

    //<editor-fold desc="Data Providers">

    /**
     * Data provider for getResourceIdFromUrlShouldIdentifyAndReturnTheIdStringFromAGivenString.
     *
     * @return array
     */
    public function urlIdStringProvider(): array
    {
        return [
            ['s-test-1234', 'https://myurl.test/s-test-1234', 'test'],
            ['p-foo-99988776655', 'https://myurl.test/p-foo-99988776655', 'foo'],
            ['s-bar-123456787', 'https://myurl.test/s-test-1234/s-bar-123456787', 'bar']
        ];
    }

    /**
     * Data provider for getResourceIdFromUrlShouldThrowExceptionIfTheIdCanNotBeFound.
     *
     * @return array
     */
    public function failingUrlIdStringProvider(): array
    {
        return[
            ['https://myurl.test/s-test-1234', 'aut'],
            ['https://myurl.test/authorizep-aut-99988776655', 'foo'],
            ['https://myurl.test/s-test-1234/z-bar-123456787', 'bar']
        ];
    }

    /**
     * Data provider for getResourceShouldFetchIfTheResourcesIdIsSetAndItHasNotBeenFetchedBefore.
     *
     * @return array
     *
     * @throws Exception
     */
    public function getResourceFetchCallDataProvider(): array
    {
        return [
            'fetchedAt is null, Id is null' => [new Customer(), 0],
            'fetchedAt is null, id is set' => [(new Customer())->setId('testId'), 1],
            'fetchedAt is set, id is null' => [(new Customer())->setFetchedAt(new DateTime('now')), 0],
            'fetchedAt is set, id is set' => [(new Customer())->setFetchedAt(new DateTime('now'))->setId('testId'), 0]
        ];
    }

    /**
     * Data provider for fetchPaymentTypeShouldCreateCorrectPaymentInstanceDependingOnId.
     *
     * @return array
     */
    public function paymentTypeAndIdProvider(): array
    {
        return [
            'Card sandbox' => [Card::class, 's-crd-12345678'],
            'Giropay sandbox' => [Giropay::class, 's-gro-12345678'],
            'Ideal sandbox' => [Ideal::class, 's-idl-12345678'],
            'Invoice sandbox' => [Invoice::class, 's-ivc-12345678'],
            'InvoiceGuaranteed sandbox' => [InvoiceGuaranteed::class, 's-ivg-12345678'],
            'Paypal sandbox' => [Paypal::class, 's-ppl-12345678'],
            'Prepayment sandbox' => [Prepayment::class, 's-ppy-12345678'],
            'Przelewy24 sandbox' => [Przelewy24::class, 's-p24-12345678'],
            'SepaDirectDebit sandbox' => [SepaDirectDebit::class, 's-sdd-12345678'],
            'SepaDirectDebitGuaranteed sandbox' => [SepaDirectDebitGuaranteed::class, 's-ddg-12345678'],
            'Sofort sandbox' => [Sofort::class, 's-sft-12345678'],
            'PIS sandbox' => [PIS::class, 's-pis-12345678'],
            'EPS sandbox' => [EPS::class, 's-eps-12345678'],
            'Alipay sandbox' => [Alipay::class, 's-ali-12345678'],
            'Wechatpay sandbox' => [Wechatpay::class, 's-wcp-12345678'],
            'Invoice factoring sandbox' => [InvoiceFactoring::class, 's-ivf-12345678'],
            'Card production' => [Card::class, 'p-crd-12345678'],
            'Giropay production' => [Giropay::class, 'p-gro-12345678'],
            'Ideal production' => [Ideal::class, 'p-idl-12345678'],
            'Invoice production' => [Invoice::class, 'p-ivc-12345678'],
            'InvoiceGuaranteed production' => [InvoiceGuaranteed::class, 'p-ivg-12345678'],
            'Paypal production' => [Paypal::class, 'p-ppl-12345678'],
            'Prepayment production' => [Prepayment::class, 'p-ppy-12345678'],
            'Przelewy24 production' => [Przelewy24::class, 'p-p24-12345678'],
            'SepaDirectDebit production' => [SepaDirectDebit::class, 'p-sdd-12345678'],
            'SepaDirectDebitGuaranteed production' => [SepaDirectDebitGuaranteed::class, 'p-ddg-12345678'],
            'Sofort production' => [Sofort::class, 'p-sft-12345678'],
            'EPS production' => [EPS::class, 'p-eps-12345678'],
            'Alipay production' => [Alipay::class, 'p-ali-12345678'],
            'Wechatpay production' => [Wechatpay::class, 'p-wcp-12345678'],
            'Invoice factoring production' => [InvoiceFactoring::class, 'p-ivf-12345678']
        ];
    }

    /**
     * Data provider for fetchPaymentTypeShouldThrowExceptionOnInvalidTypeId.
     *
     * @return array
     */
    public function paymentTypeIdProviderInvalid(): array
    {
        return [
            ['z-crd-12345678123'],
            ['p-xyz-123456ss78a'],
            ['scrd-1234567sfsbc'],
            ['p-crd12345678abc'],
            ['pcrd12345678abc'],
            ['myId'],
            [null],
            ['']
        ];
    }

    /**
     * Provides test data sets for fetchResourceByUrlShouldFetchTheDesiredResource.
     *
     * @return array
     */
    public function fetchResourceByUrlShouldFetchTheDesiredResourceDP(): array
    {
        return [
            'Authorization' => [
                'fetchAuthorization',
                ['s-pay-100746'],
                'https://api.heidelpay.com/v1/payments/s-pay-100746/authorize/s-aut-1/'
            ],
            'Charge' => [
                'fetchChargeById',
                ['s-pay-100798', 's-chg-1'],
                'https://api.heidelpay.com/v1/payments/s-pay-100798/charges/s-chg-1/'
            ],
            'Shipment' => [
                'fetchShipment',
                ['s-pay-100801', 's-shp-1'],
                'https://api.heidelpay.com/v1/payments/s-pay-100801/shipments/s-shp-1/'
            ],
            'Refund' => [
                'fetchRefundById',
                ['s-pay-100802', 's-chg-1', 's-cnl-1'],
                'https://api.heidelpay.com/v1/payments/s-pay-100802/charges/s-chg-1/cancels/s-cnl-1/'
            ],
            'Reversal' => [
                'fetchReversal',
                ['s-pay-100803', 's-cnl-1'],
                'https://api.heidelpay.com/v1/payments/s-pay-100803/authorize/s-aut-1/cancels/s-cnl-1/'
            ],
            'Payment' => [
                'fetchPayment',
                ['s-pay-100801'],
                'https://api.heidelpay.com/v1/payments/s-pay-100801'
            ],
            'Metadata' => [
                'fetchMetadata',
                ['s-mtd-6glqv9axjpnc'],
                'https://api.heidelpay.com/v1/metadata/s-mtd-6glqv9axjpnc/'
            ],
            'Customer' => [
                'fetchCustomer',
                ['s-cst-50c14d49e2fe'],
                'https://api.heidelpay.com/v1/customers/s-cst-50c14d49e2fe'
            ],
            'Basket' => [
                'fetchBasket',
                ['s-bsk-1254'],
                'https://api.heidelpay.com/v1/baskets/s-bsk-1254/'
            ]
        ];
    }

    /**
     * Data provider for fetchResourceByUrlForAPaymentTypeShouldCallFetchPaymentType.
     *
     * @return array
     */
    public function fetchResourceByUrlForAPaymentTypeShouldCallFetchPaymentTypeDP(): array
    {
        return [
            'CARD'                         => [
                's-crd-xen2ybcovn56',
                'https://api.heidelpay.com/v1/types/card/s-crd-xen2ybcovn56/'
            ],
            'GIROPAY'                      => [
                's-gro-xen2ybcovn56',
                'https://api.heidelpay.com/v1/types/giropay/s-gro-xen2ybcovn56/'
            ],
            'IDEAL'                        => [
                's-idl-xen2ybcovn56',
                'https://api.heidelpay.com/v1/types/ideal/s-idl-xen2ybcovn56/'
            ],
            'INVOICE'                      => [
                's-ivc-xen2ybcovn56',
                'https://api.heidelpay.com/v1/types/invoice/s-ivc-xen2ybcovn56/'
            ],
            'INVOICE_GUARANTEED'           => [
                's-ivg-xen2ybcovn56',
                'https://api.heidelpay.com/v1/types/invoice-guaranteed/s-ivg-xen2ybcovn56/'
            ],
            'PAYPAL'                       => [
                's-ppl-xen2ybcovn56',
                'https://api.heidelpay.com/v1/types/paypal/s-ppl-xen2ybcovn56/'
            ],
            'PREPAYMENT'                   => [
                's-ppy-xen2ybcovn56',
                'https://api.heidelpay.com/v1/types/prepayment/s-ppy-xen2ybcovn56/'
            ],
            'PRZELEWY24'                   => [
                's-p24-xen2ybcovn56',
                'https://api.heidelpay.com/v1/types/przelewy24/s-p24-xen2ybcovn56/'
            ],
            'SEPA_DIRECT_DEBIT_GUARANTEED' => [
                's-ddg-xen2ybcovn56',
                'https://api.heidelpay.com/v1/types/direct-debit-guaranteed/s-ddg-xen2ybcovn56/'
            ],
            'SEPA_DIRECT_DEBIT'            => [
                's-sdd-xen2ybcovn56',
                'https://api.heidelpay.com/v1/types/direct-debit/s-sdd-xen2ybcovn56/'
            ],
            'SOFORT'                       => [
                's-sft-xen2ybcovn56',
                'https://api.heidelpay.com/v1/types/sofort/s-sft-xen2ybcovn56/'
            ],
            'PIS'                          => [
                's-pis-xen2ybcovn56',
                'https://api.heidelpay.com/v1/types/pis/s-pis-xen2ybcovn56/'
            ],
            'EPS'                          => [
                's-eps-xen2ybcovn56',
                'https://api.heidelpay.com/v1/types/eps/s-eps-xen2ybcovn56/'
            ],
            'ALIPAY'                       => [
                's-ali-xen2ybcovn56',
                'https://api.heidelpay.com/v1/types/alipay/s-ali-xen2ybcovn56/'
            ],
            'WECHATPAY'                    => [
                's-wcp-xen2ybcovn56',
                'https://api.heidelpay.com/v1/types/wechatpay/s-wcp-xen2ybcovn56/'
            ],
            'INVOICE_FACTORING'            => [
                's-ivf-xen2ybcovn56',
                'https://api.heidelpay.com/v1/types/wechatpay/s-ivf-xen2ybcovn56/'
            ]
        ];
    }

    //</editor-fold>
}
