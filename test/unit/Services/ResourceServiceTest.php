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
 * @package  heidelpay/mgw_sdk/test/unit
 */
namespace heidelpay\MgwPhpSdk\test\unit\Services;

use heidelpay\MgwPhpSdk\Adapter\HttpAdapterInterface;
use heidelpay\MgwPhpSdk\Constants\ApiResponseCodes;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Resources\AbstractHeidelpayResource;
use heidelpay\MgwPhpSdk\Resources\Customer;
use heidelpay\MgwPhpSdk\Resources\Keypair;
use heidelpay\MgwPhpSdk\Resources\Metadata;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\BasePaymentType;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Card;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Giropay;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Ideal;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Invoice;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\InvoiceGuaranteed;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Paypal;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\PIS;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Prepayment;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Przelewy24;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\SepaDirectDebit;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\SepaDirectDebitGuaranteed;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Sofort;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Cancellation;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Shipment;
use heidelpay\MgwPhpSdk\Services\HttpService;
use heidelpay\MgwPhpSdk\Services\ResourceService;
use heidelpay\MgwPhpSdk\test\BaseUnitTest;
use heidelpay\MgwPhpSdk\test\unit\DummyResource;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\RuntimeException;

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
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function getResourceIdFromUrlShouldIdentifyAndReturnTheIdStringFromAGivenString($expected, $uri, $idString)
    {
        $resourceService = new ResourceService(new Heidelpay('s-priv-123'));
        $this->assertEquals($expected, $resourceService->getResourceIdFromUrl($uri, $idString));
    }

    /**
     * Verify getResourceIdFromUrl throws exception if the id cannot be found.
     *
     * @test
     * @dataProvider failingUrlIdStringProvider
     *
     * @throws \RuntimeException
     *
     * @param mixed $uri
     * @param mixed $idString
     */
    public function getResourceIdFromUrlShouldThrowExceptionIfTheIdCanNotBeFound($uri, $idString)
    {
        $resourceService = new ResourceService(new Heidelpay('s-priv-123'));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Id not found!');
        $resourceService->getResourceIdFromUrl($uri, $idString);
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
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function createShouldCallSendAndThenHandleResponseWithTheResponseData()
    {
        $response = new \stdClass();
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
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function createShouldNotHandleResponseWithError()
    {
        $response = new \stdClass();
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
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function updateShouldCallSendAndThenHandleResponseWithTheResponseData()
    {
        $response = new \stdClass();

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
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function updateShouldNotHandleResponseWithError()
    {
        $response = new \stdClass();
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
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function deleteShouldCallSendAndThenSetTheResourceNull()
    {
        $response = new \stdClass();

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
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function deleteShouldNotDeleteObjectOnResponseWithError()
    {
        $response = new \stdClass();
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
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function fetchShouldCallSendWithGetUpdateFetchedAtAndCallHandleResponse()
    {
        $response = new \stdClass();
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

        $now = (new \DateTime('now'))->getTimestamp();
        $then = $resourceMock->getFetchedAt()->getTimestamp();
        $this->assertTrue(($now - $then) < 60);
    }

    /**
     * Verify fetchPayment method will fetch the passed payment object.
     *
     * @test
     *
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function fetchPaymentCalledWithIdShouldCreatePaymentObjectWithIdAndCallFetch()
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetch'])
            ->setConstructorArgs([$heidelpay])->getMock();
        $resourceSrvMock->expects($this->once())->method('fetch')
            ->with($this->callback(function ($payment) use ($heidelpay) {
                return $payment instanceof Payment &&
                    $payment->getId() === 'testPaymentId' &&
                    $payment->getHeidelpayObject() === $heidelpay;
            }));

        /** @var ResourceService $resourceSrvMock */
        $resourceSrvMock->fetchPayment('testPaymentId');
    }

    /**
     * Verify fetchKeypair will call fetch with a Keypair object.
     *
     * @test
     *
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function fetchKeypairShouldCallFetchWithAKeypairObject()
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetch'])
            ->setConstructorArgs([$heidelpay])->getMock();
        $resourceSrvMock->expects($this->once())->method('fetch')
            ->with($this->callback(function ($keypair) use ($heidelpay) {
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
     * @throws Exception
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function createPaymentTypeShouldSetHeidelpayObjectAndCallCreate()
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $paymentType = new Sofort();

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['create'])
            ->setConstructorArgs([$heidelpay])->getMock();
        $resourceSrvMock->expects($this->once())->method('create')
            ->with($this->callback(function ($type) use ($heidelpay, $paymentType) {
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
     * @throws \RuntimeException
     * @throws \ReflectionException
     * @throws HeidelpayApiException
     */
    public function fetchPaymentTypeShouldFetchCorrectPaymentInstanceDependingOnId($typeClass, $typeId)
    {
        $heidelpay = new Heidelpay('s-priv-1234');

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetch'])
            ->setConstructorArgs([$heidelpay])->getMock();
        $resourceSrvMock->expects($this->once())->method('fetch')
            ->with($this->callback(function ($type) use ($heidelpay, $typeClass, $typeId) {
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
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function fetchPaymentTypeShouldThrowExceptionOnInvalidTypeId($typeId)
    {
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetch'])
            ->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->never())->method('fetch');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid payment type!');

        /** @var ResourceService $resourceSrvMock */
        $resourceSrvMock->fetchPaymentType($typeId);
    }

    /**
     * Verify createCustomer calls create with customer object and the heidelpay resource is set.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function createCustomerShouldCallCreateWithCustomerObjectAndSetHeidelpayReference()
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $customer = new Customer();

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['create'])
            ->setConstructorArgs([$heidelpay])->getMock();
        $resourceSrvMock->expects($this->once())->method('create')
            ->with($this->callback(function ($resource) use ($heidelpay, $customer) {
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
     * @throws Exception
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
            ->with($this->callback(function ($customerToFetch) use ($customer) {
                /** @var Customer $customerToFetch */
                return $customerToFetch !== $customer &&
                       $customerToFetch->getId() === $customer->getId() &&
                       $customerToFetch->getCustomerId() === $customer->getCustomerId();
            }))->willReturn($fetchedCustomer);
        $resourceSrvMock->expects($this->once())->method('updateCustomer')
            ->with($this->callback(function ($customerToUpdate) use ($customer) {
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
     * @throws Exception
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
        } catch (\RuntimeException $e) {
            $this->assertInstanceOf(\RuntimeException::class, $e);
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
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function fetchCustomerShouldCallFetchWithNewCustomerObject()
    {
        $heidelpay = new Heidelpay('s-priv-123');

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetch'])
            ->setConstructorArgs([$heidelpay])->getMock();
        $resourceSrvMock->expects($this->once())->method('fetch')->with(
            $this->callback(function ($param) use ($heidelpay) {
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
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * Verify deleteCustomer cals fetchCustomer with id if the customer object is referenced by id.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * Verify fetchMetadata calls fetch with a new metadata object with the given id.
     *
     * @test
     *
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function fetchMetadataShouldCallFetchWithANewMetadataObjectWithTheGivenId()
    {
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetch'])
            ->disableOriginalConstructor()->getMock();

        $resourceSrvMock->expects($this->once())->method('fetch')->with(
            $this->callback(function ($metadata) {
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
     * @throws \RuntimeException
     * @throws \ReflectionException
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
     * @throws \Exception
     */
    public function getResourceFetchCallDataProvider(): array
    {
        return [
            'fetchedAt is null, Id is null' => [new Customer(), 0],
            'fetchedAt is null, id is set' => [(new Customer())->setId('testId'), 1],
            'fetchedAt is set, id is null' => [(new Customer())->setFetchedAt(new \DateTime('now')), 0],
            'fetchedAt is set, id is set' => [(new Customer())->setFetchedAt(new \DateTime('now'))->setId('testId'), 0],
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
            'PIS production' => [PIS::class, 'p-pis-12345678']
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

    //</editor-fold>
}
