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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\test\unit
 */
namespace heidelpayPHP\test\unit\Services;

use DateTime;
use Exception;
use heidelpayPHP\Adapter\HttpAdapterInterface;
use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Interfaces\ResourceServiceInterface;
use heidelpayPHP\Resources\AbstractHeidelpayResource;
use heidelpayPHP\Resources\Basket;
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\CustomerFactory;
use heidelpayPHP\Resources\Keypair;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\Alipay;
use heidelpayPHP\Resources\PaymentTypes\Card;
use heidelpayPHP\Resources\PaymentTypes\EPS;
use heidelpayPHP\Resources\PaymentTypes\Giropay;
use heidelpayPHP\Resources\PaymentTypes\HirePurchaseDirectDebit;
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
use heidelpayPHP\Resources\Recurring;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\Resources\TransactionTypes\Payout;
use heidelpayPHP\Resources\TransactionTypes\Shipment;
use heidelpayPHP\Services\HttpService;
use heidelpayPHP\Services\IdService;
use heidelpayPHP\Services\ResourceService;
use heidelpayPHP\test\BasePaymentTest;
use heidelpayPHP\test\unit\DummyResource;
use heidelpayPHP\test\unit\Traits\TraitDummyCanRecur;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;
use RuntimeException;
use stdClass;

class ResourceServiceTest extends BasePaymentTest
{
    //<editor-fold desc="General">

    /**
     * Verify setters and getters work properly.
     *
     * @test
     *
     * @throws RuntimeException
     */
    public function gettersAndSettersShouldWorkProperly()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        /** @var ResourceService $resourceService */
        $resourceService = $heidelpay->getResourceService();
        $this->assertSame($heidelpay, $resourceService->getHeidelpay());

        $heidelpay2 = new Heidelpay('s-priv-1234');
        $resourceService->setHeidelpay($heidelpay2);
        $this->assertSame($heidelpay2, $resourceService->getHeidelpay());
    }

    /**
     * Verify send will call send on httpService.
     *
     * @test
     * @dataProvider sendShouldCallSendOnHttpServiceDP
     *
     * @param string $method
     * @param string $uri
     * @param bool   $appendId
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     */
    public function sendShouldCallSendOnHttpService(string $method, string $uri, bool $appendId)
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $resourceMock = $this->getMockBuilder(DummyResource::class)->setMethods(['getUri', 'getHeidelpayObject'])->getMock();
        $resourceMock->expects($this->once())->method('getUri')->with($appendId)->willReturn($uri);
        $resourceMock->method('getHeidelpayObject')->willReturn($heidelpay);
        $httpSrvMock = $this->getMockBuilder(HttpService::class)->setMethods(['send'])->getMock();
        $resourceSrv = new ResourceService($heidelpay);

        /** @var HttpService $httpSrvMock */
        $heidelpay->setHttpService($httpSrvMock);
        $httpSrvMock->expects($this->once())->method('send')->with($uri, $resourceMock, $method)->willReturn('{"response": "This is the response"}');

        /** @var AbstractHeidelpayResource $resourceMock */
        $response = $resourceSrv->send($resourceMock, $method);
        $this->assertEquals('This is the response', $response->response);
    }

    //</editor-fold>

    //<editor-fold desc="ResourceId">

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

    //</editor-fold>

    //<editor-fold desc="CRUD">

    /**
     * Verify fetchResource calls fetch if its id is set and it has never been fetched before.
     *
     * @test
     * @dataProvider fetchResourceFetchCallDP
     *
     * @param $resource
     * @param $timesFetchIsCalled
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchResourceIfTheResourcesIdIsSetAndItHasNotBeenFetchedBefore($resource, $timesFetchIsCalled)
    {
        /** @var ResourceService|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchResource'])->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->exactly($timesFetchIsCalled))->method('fetchResource')->with($resource);

        $resourceSrvMock->getResource($resource);
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

        /** @var Customer|MockObject $testResource */
        $testResource = $this->getMockBuilder(Customer::class)->setMethods(['handleResponse'])->getMock();
        $testResource->expects($this->once())->method('handleResponse')->with($response, HttpAdapterInterface::REQUEST_POST);

        /** @var ResourceService|MockObject $resourceServiceMock */
        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)->setMethods(['send'])->disableOriginalConstructor()->getMock();
        $resourceServiceMock->expects($this->once())->method('send')->with($testResource, HttpAdapterInterface::REQUEST_POST)->willReturn($response);

        $this->assertSame($testResource, $resourceServiceMock->createResource($testResource));
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

        /** @var Customer|MockObject $testResource */
        $testResource = $this->getMockBuilder(Customer::class)->setMethods(['handleResponse'])->getMock();
        $testResource->expects($this->never())->method('handleResponse');

        /** @var ResourceService|MockObject $resourceServiceMock */
        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)->setMethods(['send'])->disableOriginalConstructor()->getMock();
        $resourceServiceMock->expects($this->once())->method('send')->with($testResource, HttpAdapterInterface::REQUEST_POST)->willReturn($response);

        $this->assertSame($testResource, $resourceServiceMock->createResource($testResource));
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

        /** @var Customer|MockObject $testResource */
        $testResource = $this->getMockBuilder(Customer::class)->setMethods(['handleResponse'])->getMock();
        $testResource->expects($this->once())->method('handleResponse')->with($response, HttpAdapterInterface::REQUEST_PUT);

        /** @var ResourceService|MockObject $resourceServiceMock */
        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)->setMethods(['send'])->disableOriginalConstructor()->getMock();
        $resourceServiceMock->expects($this->once())->method('send')->with($testResource, HttpAdapterInterface::REQUEST_PUT)->willReturn($response);

        $this->assertSame($testResource, $resourceServiceMock->updateResource($testResource));
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
        /** @var Customer|MockObject $testResource */
        $testResource = $this->getMockBuilder(Customer::class)->setMethods(['handleResponse'])->getMock();
        $testResource->expects($this->never())->method('handleResponse');

        /** @var ResourceService|MockObject $resourceServiceMock */
        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)->setMethods(['send'])->disableOriginalConstructor()->getMock();
        $resourceServiceMock->expects($this->once())->method('send')->with($testResource, HttpAdapterInterface::REQUEST_PUT)->willReturn((object)['isError' => true]);

        $this->assertSame($testResource, $resourceServiceMock->updateResource($testResource));
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
        /** @var Customer|MockObject $testResource */
        $testResource = $this->getMockBuilder(Customer::class)->getMock();

        /** @var ResourceService|MockObject $resourceServiceMock */
        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)->setMethods(['send'])->disableOriginalConstructor()->getMock();
        $resourceServiceMock->expects($this->once())->method('send')->with($testResource, HttpAdapterInterface::REQUEST_DELETE)->willReturn(new stdClass());

        $this->assertNull($resourceServiceMock->deleteResource($testResource));
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
        /** @var Customer|MockObject $testResource */
        $testResource = $this->getMockBuilder(Customer::class)->getMock();

        /** @var ResourceService|MockObject $resourceServiceMock */
        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)->setMethods(['send'])->disableOriginalConstructor()->getMock();
        $resourceServiceMock->expects($this->once())->method('send')->with($testResource, HttpAdapterInterface::REQUEST_DELETE)->willReturn((object)['isError' => true]);

        $responseResource = $resourceServiceMock->deleteResource($testResource);
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

        /** @var AbstractHeidelpayResource|MockObject $resourceMock */
        $resourceMock = $this->getMockBuilder(Customer::class)->setMethods(['handleResponse'])->getMock();
        $resourceMock->expects($this->once())->method('handleResponse')->with($response);

        /** @var ResourceService|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['send'])->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->once())->method('send')->with($resourceMock, HttpAdapterInterface::REQUEST_GET)->willReturn($response);

        $this->assertNull($resourceMock->getFetchedAt());
        $resourceSrvMock->fetchResource($resourceMock);

        $now = (new DateTime('now'))->getTimestamp();
        $then = $resourceMock->getFetchedAt()->getTimestamp();
        $this->assertTrue(($now - $then) < 60);
    }

    /**
     * Verify fetchResourceByUrl calls fetch for the desired resource.
     *
     * @test
     * @dataProvider fetchResourceByUrlShouldFetchTheDesiredResourceDP
     *
     * @param string $fetchMethod
     * @param mixed  $arguments
     * @param string $resourceUrl
     *
     * @throws Exception
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     * @throws ReflectionException
     */
    public function fetchResourceByUrlShouldFetchTheDesiredResource($fetchMethod, $arguments, $resourceUrl)
    {
        /** @var Heidelpay|MockObject $heidelpayMock */
        $heidelpayMock = $this->getMockBuilder(Heidelpay::class)->disableOriginalConstructor()->setMethods([$fetchMethod])->getMock();
        $heidelpayMock->expects($this->once())->method($fetchMethod)->with(...$arguments);
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
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     * @throws ReflectionException
     */
    public function fetchResourceByUrlForAPaymentTypeShouldCallFetchPaymentType($paymentTypeId, $resourceUrl)
    {
        /** @var ResourceService|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()->setMethods(['fetchPaymentType'])->getMock();
        $resourceSrvMock->expects($this->once())->method('fetchPaymentType')->with($paymentTypeId);

        $resourceSrvMock->fetchResourceByUrl($resourceUrl);
    }

    /**
     * Verify does not call fetchResourceByUrl and returns null if the resource type is unknown.
     *
     * @test
     *
     * @throws Exception
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is a error while using the SDK.
     * @throws ReflectionException
     */
    public function fetchResourceByUrlForAPaymentTypeShouldReturnNullIfTheTypeIsUnknown()
    {
        /** @var ResourceService|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()->setMethods(['fetchPaymentType'])->getMock();
        $resourceSrvMock->expects($this->never())->method('fetchPaymentType');

        $this->assertNull($resourceSrvMock->fetchResourceByUrl('https://api.heidelpay.com/v1/types/card/s-unknown-xen2ybcovn56/'));
    }

    /**
     * Verify fetchPayment method will fetch the passed payment object.
     *
     * @test
     * @dataProvider fetchShouldCallFetchResourceDP
     *
     * @param string $fetchMethod
     * @param array  $arguments
     * @param mixed  $callback
     *
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws RuntimeException
     */
    public function fetchShouldCallFetchResource(string $fetchMethod, array $arguments, $callback)
    {
        $heidelpay = new Heidelpay('s-priv-1234');

        /** @var ResourceService|MockObject $resourceServiceMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchResource'])->setConstructorArgs([$heidelpay])->getMock();
        $resourceSrvMock->expects($this->once())->method('fetchResource')->with($this->callback(
            static function ($resource) use ($callback, $heidelpay) {
                /** @var AbstractHeidelpayResource $resource */
                return $callback($resource) && $resource->getHeidelpayObject() === $heidelpay;
            }
        ));

        /** @var AbstractHeidelpayResource $resource */
        $resource = $resourceSrvMock->$fetchMethod(...$arguments);
        $this->assertEquals($heidelpay, $resource->getParentResource());
        $this->assertEquals($heidelpay, $resource->getHeidelpayObject());
    }

    //</editor-fold>

    //<editor-fold desc="PaymentType">

    /**
     * Verify createPaymentType method will set parentResource to heidelpay object and call create.
     *
     * @test
     *
     * @throws ReflectionException
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function createPaymentTypeShouldSetHeidelpayObjectAndCallCreate()
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $paymentType = new Sofort();

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['createResource'])->setConstructorArgs([$heidelpay])->getMock();
        $resourceSrvMock->expects($this->once())->method('createResource')
            ->with($this->callback(static function ($type) use ($heidelpay, $paymentType) {
                return $type === $paymentType && $type->getHeidelpayObject() === $heidelpay;
            }));

        /** @var ResourceServiceInterface $resourceSrvMock */
        $returnedType = $resourceSrvMock->createPaymentType($paymentType);

        $this->assertSame($paymentType, $returnedType);
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
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchResource'])->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->never())->method('fetchResource');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid payment type!');

        /** @var ResourceServiceInterface $resourceSrvMock */
        $resourceSrvMock->fetchPaymentType($typeId);
    }

    /**
     * Update payment type should call update method.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     */
    public function updatePaymentTypeShouldCallUpdateMethod()
    {
        $paymentType = (new HirePurchaseDirectDebit())->setId('paymentTypeId');

        /** @var ResourceServiceInterface|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['updateResource'])->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->once())->method('updateResource')->with($paymentType)->willReturn($paymentType);

        $returnedPaymentType = $resourceSrvMock->updatePaymentType($paymentType);

        $this->assertSame($paymentType, $returnedPaymentType);
    }

    //</editor-fold>

    //<editor-fold desc="Customer">

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

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['createResource'])->setConstructorArgs([$heidelpay])->getMock();
        $resourceSrvMock->expects($this->once())->method('createResource')
            ->with($this->callback(static function ($resource) use ($heidelpay, $customer) {
                return $resource === $customer && $resource->getHeidelpayObject() === $heidelpay;
            }));

        /** @var ResourceServiceInterface $resourceSrvMock */
        $returnedCustomer = $resourceSrvMock->createCustomer($customer);

        $this->assertSame($customer, $returnedCustomer);
    }

    /**
     * Verify createOrUpdateCustomer method tries to fetch and update the given customer.
     *
     * @test
     *
     * @throws ReflectionException
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function createOrUpdateCustomerShouldFetchAndUpdateCustomerIfItAlreadyExists()
    {
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()->setMethods(['createCustomer', 'fetchCustomerByExtCustomerId', 'updateCustomer'])->getMock();

        // provide new data for an existing customer
        $customer = (new Customer())->setCustomerId('externalCustomerId')->setEmail('customer@email.de');

        // this customer is fetched when realized a customer with the given customerId already exists
        $fetchedCustomer = CustomerFactory::createCustomer('Max', 'Mustermann')->setCustomerId('externalCustomerId')->setId('customerId');

        // throw exception to indicate a customer with the given customerId already exists
        $resourceSrvMock->expects($this->once())->method('createCustomer')->with($customer)->willThrowException(new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_CUSTOMER_ID_ALREADY_EXISTS));

        // Expect the customer to be fetched by its customerId if it already exists and has to be updated.
        $resourceSrvMock->expects($this->once())->method('fetchCustomerByExtCustomerId')
            ->with($this->callback(static function ($customerId) use ($customer) {
                return $customerId === $customer->getCustomerId();
            }))->willReturn($fetchedCustomer);

        // Expect the fetched customer is then updated with the new data.
        $resourceSrvMock->expects($this->once())->method('updateCustomer')
            ->with($this->callback(static function ($customerToUpdate) use ($customer) {
                /** @var Customer $customerToUpdate */
                return $customerToUpdate === $customer &&
                   $customerToUpdate->getId() === $customer->getId() &&
                   $customerToUpdate->getEmail() === 'customer@email.de';
            }));

        // Make the call and assertions.
        /** @var ResourceServiceInterface $resourceSrvMock */
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
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function createOrUpdateCustomerShouldThrowTheExceptionIfItIsNotCustomerIdAlreadyExists()
    {
        $customer = (new Customer())->setCustomerId('externalCustomerId')->setEmail('customer@email.de');

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()->setMethods(['createCustomer', 'fetchCustomer', 'updateCustomer'])->getMock();

        $exc = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_CUSTOMER_ID_REQUIRED);
        $resourceSrvMock->expects($this->once())->method('createCustomer')->with($customer)->willThrowException($exc);
        $resourceSrvMock->expects($this->never())->method('fetchCustomer');
        $resourceSrvMock->expects($this->never())->method('updateCustomer');

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_CUSTOMER_ID_REQUIRED);

        /** @var ResourceServiceInterface $resourceSrvMock */
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

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchResource'])->setConstructorArgs([$heidelpay])->getMock();
        $resourceSrvMock->expects($this->once())->method('fetchResource')->with($customer);

        try {
            $customer->getHeidelpayObject();
            $this->assertTrue(false, 'This exception should have been thrown!');
        } catch (RuntimeException $e) {
            $this->assertInstanceOf(RuntimeException::class, $e);
            $this->assertEquals('Parent resource reference is not set!', $e->getMessage());
        }

        /** @var ResourceServiceInterface $resourceSrvMock */
        $returnedCustomer = $resourceSrvMock->fetchCustomer($customer);
        $this->assertSame($customer, $returnedCustomer);
        $this->assertSame($heidelpay, $customer->getHeidelpayObject());
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

        /** @var ResourceServiceInterface|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['updateResource'])->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->once())->method('updateResource')->with($customer);

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

        /** @var ResourceServiceInterface|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['deleteResource'])->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->once())->method('deleteResource')->with($customer);

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
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['deleteResource', 'fetchCustomer'])->disableOriginalConstructor()->getMock();
        $customer = CustomerFactory::createCustomer('Max', 'Mustermann');
        $resourceSrvMock->expects($this->once())->method('fetchCustomer')->with('myCustomerId')->willReturn($customer);
        $resourceSrvMock->expects($this->once())->method('deleteResource')->with($customer);

        /** @var ResourceServiceInterface $resourceSrvMock */
        $returnedCustomer = $resourceSrvMock->deleteCustomer('myCustomerId');
        $this->assertSame($customer, $returnedCustomer);
    }

    //</editor-fold>

    //<editor-fold desc="Authorization">

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
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchPayment', 'fetchResource'])->disableOriginalConstructor()->getMock();
        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getAuthorization'])->getMock();

        $authorize = (new Authorization())->setId('s-aut-1');

        /** @var ResourceServiceInterface|MockObject $resourceSrvMock */
        $resourceSrvMock->expects($this->once())->method('fetchPayment')->with($paymentMock)->willReturn($paymentMock);
        $paymentMock->expects($this->once())->method('getAuthorization')->willReturn($authorize);
        $resourceSrvMock->expects($this->once())->method('fetchResource')->with($authorize)->willReturn($authorize);

        $returnedAuthorize = $resourceSrvMock->fetchAuthorization($paymentMock);
        $this->assertSame($authorize, $returnedAuthorize);
    }

    /**
     * Verify fetchAuthorization will throw runtime error if the given payment does not seem to have an authorization.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     */
    public function fetchAuthorizationShouldThrowExceptionIfNoAuthorizationIsPresent()
    {
        /** @var MockObject|ResourceService $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()->setMethods(['fetchPayment'])->getMock();

        $payment = new Payment();
        $resourceSrvMock->expects($this->once())->method('fetchPayment')->willReturn($payment);

        $this->assertNull($payment->getAuthorization());
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The payment does not seem to have an Authorization.');

        $resourceSrvMock->fetchAuthorization('paymentId');
    }

    //</editor-fold>

    //<editor-fold desc="Payout">

    /**
     * Verify fetchPayout fetches payment object and returns its payout.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchPayoutShouldFetchPaymentAndReturnItsPayout()
    {
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchPayment', 'fetchResource'])->disableOriginalConstructor()->getMock();
        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getPayout'])->getMock();

        $payout = (new Payout())->setId('s-out-1');
        $resourceSrvMock->expects($this->once())->method('fetchPayment')->with($paymentMock)->willReturn($paymentMock);
        $paymentMock->expects($this->once())->method('getPayout')->willReturn($payout);
        $resourceSrvMock->expects($this->once())->method('fetchResource')->with($payout)->willReturn($payout);

        /** @var ResourceServiceInterface $resourceSrvMock */
        $returnedPayout = $resourceSrvMock->fetchPayout($paymentMock);
        $this->assertSame($payout, $returnedPayout);
    }

    //</editor-fold>

    //<editor-fold desc="Charge">

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
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchPayment', 'fetchResource'])->disableOriginalConstructor()->getMock();
        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getCharge'])->getMock();

        $charge = (new Charge())->setId('chargeId');
        $paymentMock->expects($this->once())->method('getCharge')->with('chargeId')->willReturn($charge);

        /** @var ResourceServiceInterface|MockObject $resourceSrvMock */
        $resourceSrvMock->expects($this->once())->method('fetchPayment')->with($paymentMock)->willReturn($paymentMock);
        $resourceSrvMock->expects($this->once())->method('fetchResource')->with($charge)->willReturn($charge);

        $returnedCharge = $resourceSrvMock->fetchChargeById($paymentMock, 'chargeId');
        $this->assertSame($charge, $returnedCharge);
    }

    /**
     * Verify fetchCharge fetches payment object and gets and returns the charge object from it.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchChargeShouldFetchPaymentAndReturnTheChargeOfThePayment()
    {
        /** @var ResourceService|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchResource'])->disableOriginalConstructor()->getMock();
        $charge = (new Charge())->setId('chargeId');

        $resourceSrvMock->expects($this->once())->method('fetchResource')->with($charge)->willReturn($charge);

        $this->assertSame($charge, $resourceSrvMock->fetchCharge($charge));
    }

    /**
     * Verify fetchChargeById throws exception if the charge can not be found.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function fetchChargeByIdShouldThrowExceptionIfChargeDoesNotExist()
    {
        /** @var MockObject|Payment $paymentMock */
        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getCharge'])->getMock();
        $paymentMock->expects($this->once())->method('getCharge')->with('chargeId')->willReturn(null);

        /** @var MockObject|ResourceService $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchPayment'])->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->once())->method('fetchPayment')->with($paymentMock)->willReturn($paymentMock);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The charge object could not be found.');
        $resourceSrvMock->fetchChargeById($paymentMock, 'chargeId');
    }

    //</editor-fold>

    //<editor-fold desc="Cancel">

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
        /** @var ResourceServiceInterface|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchResource'])->disableOriginalConstructor()->getMock();
        /** @var Authorization|MockObject $authorizeMock */
        $authorizeMock = $this->getMockBuilder(Authorization::class)->setMethods(['getCancellation'])->getMock();

        $cancellation = new Cancellation();
        $resourceSrvMock->expects($this->once())->method('fetchResource')->with($authorizeMock);
        $authorizeMock->expects($this->once())->method('getCancellation')->with('cancelId')->willReturn($cancellation);

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
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchPayment'])->disableOriginalConstructor()->getMock();
        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getAuthorization'])->getMock();
        $authorizationMock = $this->getMockBuilder(Authorization::class)->setMethods(['getCancellation'])->getMock();

        $cancel = (new Cancellation())->setId('cancelId');
        $resourceSrvMock->expects($this->once())->method('fetchPayment')->willReturn($paymentMock);
        $paymentMock->expects($this->once())->method('getAuthorization')->willReturn($authorizationMock);
        $authorizationMock->expects($this->once())->method('getCancellation')->willReturn($cancel);

        /** @var ResourceServiceInterface $resourceSrvMock */
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
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchChargeById', 'fetchRefund'])->disableOriginalConstructor()->getMock();

        $charge = (new Charge())->setId('chargeId');
        $cancel = (new Cancellation())->setId('cancellationId');

        /** @var ResourceServiceInterface|MockObject $resourceSrvMock */
        $resourceSrvMock->expects($this->once())->method('fetchChargeById')->with('paymentId', 'chargeId')->willReturn($charge);
        $resourceSrvMock->expects($this->once())->method('fetchRefund')->with($charge, 'cancellationId')->willReturn($cancel);

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
        /** @var ResourceServiceInterface|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchResource'])->disableOriginalConstructor()->getMock();
        $cancel = (new Cancellation())->setId('cancellationId');
        $chargeMock = $this->getMockBuilder(Charge::class)->setMethods(['getCancellation'])->getMock();
        $chargeMock->expects($this->once())->method('getCancellation')->with('cancellationId', true)->willReturn($cancel);
        $resourceSrvMock->expects($this->once())->method('fetchResource')->with($cancel)->willReturn($cancel);

        /** @var Charge          $chargeMock*/
        $returnedCancellation = $resourceSrvMock->fetchRefund($chargeMock, 'cancellationId');
        $this->assertSame($cancel, $returnedCancellation);
    }

    //</editor-fold>

    //<editor-fold desc="Shipment">

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
        /** @var ResourceServiceInterface|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchPayment'])->disableOriginalConstructor()->getMock();
        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getShipment'])->getMock();
        $shipment = (new Shipment())->setId('shipmentId');
        $resourceSrvMock->expects($this->once())->method('fetchPayment')->with('paymentId')->willReturn($paymentMock);
        $paymentMock->expects($this->once())->method('getShipment')->with('shipmentId', false)->willReturn($shipment);

        /** @var Payment         $paymentMock */
        $returnedShipment = $resourceSrvMock->fetchShipment('paymentId', 'shipmentId');
        $this->assertSame($shipment, $returnedShipment);
    }

    //</editor-fold>

    //<editor-fold desc="Metadata">

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
        /** @var ResourceServiceInterface|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['createResource'])->disableOriginalConstructor()->getMock();
        $metadata = new Metadata();
        $resourceSrvMock->expects($this->once())->method('createResource')->with($metadata);

        $this->assertSame($metadata, $resourceSrvMock->createMetadata($metadata));
    }

    //</editor-fold>

    //<editor-fold desc="Basket">

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
        /** @var ResourceServiceInterface|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setConstructorArgs([$heidelpay])->setMethods(['createResource'])->getMock();
        $resourceSrvMock->expects($this->once())->method('createResource');

        $basket = new Basket();
        try {
            $basket->getParentResource();
            $this->assertTrue(false, 'This exception should have been thrown!');
        } catch (RuntimeException $e) {
            $this->assertEquals('Parent resource reference is not set!', $e->getMessage());
        }

        $this->assertSame($basket, $resourceSrvMock->createBasket($basket));
        $this->assertSame($heidelpay, $basket->getParentResource());
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
        /** @var ResourceServiceInterface|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setConstructorArgs([$heidelpay])->setMethods(['updateResource'])->getMock();
        $basket = new Basket();
        $resourceSrvMock->expects($this->once())->method('updateResource')->with($basket);

        $returnedBasket = $resourceSrvMock->updateBasket($basket);

        $this->assertSame($basket, $returnedBasket);
        $this->assertEquals($heidelpay, $basket->getParentResource());
        $this->assertEquals($heidelpay, $basket->getHeidelpayObject());
    }

    //</editor-fold>

    //<editor-fold desc="Recurring">

    /**
     * Verify createRecurring calls fetch for the payment type if it is given the id.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     */
    public function createRecurringShouldFetchThePaymentTypeById()
    {
        $paymentType = new TraitDummyCanRecur();

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()->setMethods(['fetchPaymentType', 'createResource'])->getMock();
        $resourceServiceMock->expects(self::once())->method('fetchPaymentType')->with('typeId')->willReturn($paymentType);
        $resourceServiceMock->expects(self::once())->method('createResource')
            ->with($this::callback(static function ($data) {
                return $data instanceof Recurring && $data->getReturnUrl() === 'returnUrl' && $data->getPaymentTypeId() === 'myId';
            }));

        /** @var ResourceServiceInterface $resourceServiceMock */
        $resourceServiceMock->activateRecurringPayment('typeId', 'returnUrl');
    }

    /**
     * Verify createRecurring does not call fetch for the payment type if it is given the object itself.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws \PHPUnit\Framework\Exception
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     */
    public function createRecurringShouldNotFetchThePaymentTypeByObject()
    {
        $paymentType = new TraitDummyCanRecur();
        /** @var ResourceServiceInterface|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()->setMethods(['fetchPaymentType', 'createResource'])->getMock();
        $resourceSrvMock->expects(self::never())->method('fetchPaymentType');
        $resourceSrvMock->expects(self::once())->method('createResource')
            ->with($this::callback(static function ($data) {
                return $data instanceof Recurring && $data->getReturnUrl() === 'returnUrl' && $data->getPaymentTypeId() === 'myId';
            }));

        $resourceSrvMock->activateRecurringPayment($paymentType, 'returnUrl');
    }

    /**
     * Verify createRecurring throws exception if it is called with a payment type which does not support recurring payment.
     *
     * @test
     *
     * @throws HeidelpayApiException        A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException             A RuntimeException is thrown when there is an error while using the SDK.
     * @throws \PHPUnit\Framework\Exception
     */
    public function createRecurringShouldThrowExceptionWhenRecurringPaymentIsNotSupportedByType()
    {
        $resourceService = new ResourceService(new Heidelpay('s-priv-123'));
        $this->expectException(RuntimeException::class);

        $resourceService->activateRecurringPayment(new Sofort(), 'returnUrl');
    }

    //</editor-fold>

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
    public function fetchResourceFetchCallDP(): array
    {
        return [
            'fetchedAt is null, Id is null' => [new Customer(), 0],
            'fetchedAt is null, id is set' => [(new Customer())->setId('testId'), 1],
            'fetchedAt is set, id is null' => [(new Customer())->setFetchedAt(new DateTime('now')), 0],
            'fetchedAt is set, id is set' => [(new Customer())->setFetchedAt(new DateTime('now'))->setId('testId'), 0]
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
            'Authorization' => ['fetchAuthorization', ['s-pay-100746'], 'https://api.heidelpay.com/v1/payments/s-pay-100746/authorize/s-aut-1/'],
            'Charge'        => ['fetchChargeById', ['s-pay-100798', 's-chg-1'], 'https://api.heidelpay.com/v1/payments/s-pay-100798/charges/s-chg-1/'],
            'Shipment'      => ['fetchShipment', ['s-pay-100801', 's-shp-1'], 'https://api.heidelpay.com/v1/payments/s-pay-100801/shipments/s-shp-1/'],
            'Refund'        => ['fetchRefundById', ['s-pay-100802', 's-chg-1', 's-cnl-1'], 'https://api.heidelpay.com/v1/payments/s-pay-100802/charges/s-chg-1/cancels/s-cnl-1/'],
            'Reversal'      => ['fetchReversal', ['s-pay-100803', 's-cnl-1'], 'https://api.heidelpay.com/v1/payments/s-pay-100803/authorize/s-aut-1/cancels/s-cnl-1/'],
            'Payment'       => ['fetchPayment', ['s-pay-100801'], 'https://api.heidelpay.com/v1/payments/s-pay-100801'],
            'Metadata'      => ['fetchMetadata', ['s-mtd-6glqv9axjpnc'], 'https://api.heidelpay.com/v1/metadata/s-mtd-6glqv9axjpnc/'],
            'Customer'      => ['fetchCustomer', ['s-cst-50c14d49e2fe'], 'https://api.heidelpay.com/v1/customers/s-cst-50c14d49e2fe'],
            'Basket'        => ['fetchBasket', ['s-bsk-1254'], 'https://api.heidelpay.com/v1/baskets/s-bsk-1254/'],
            'Payout'        => ['fetchPayout', ['s-pay-100746'], 'https://api.heidelpay.com/v1/payments/s-pay-100746/payout/s-out-1/']
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
            'CARD'                         => ['s-crd-xen2ybcovn56', 'https://api.heidelpay.com/v1/types/card/s-crd-xen2ybcovn56/'],
            'GIROPAY'                      => ['s-gro-xen2ybcovn56', 'https://api.heidelpay.com/v1/types/giropay/s-gro-xen2ybcovn56/'],
            'IDEAL'                        => ['s-idl-xen2ybcovn56', 'https://api.heidelpay.com/v1/types/ideal/s-idl-xen2ybcovn56/'],
            'INVOICE'                      => ['s-ivc-xen2ybcovn56', 'https://api.heidelpay.com/v1/types/invoice/s-ivc-xen2ybcovn56/'],
            'INVOICE_GUARANTEED'           => ['s-ivg-xen2ybcovn56', 'https://api.heidelpay.com/v1/types/invoice-guaranteed/s-ivg-xen2ybcovn56/'],
            'PAYPAL'                       => ['s-ppl-xen2ybcovn56', 'https://api.heidelpay.com/v1/types/paypal/s-ppl-xen2ybcovn56/'],
            'PREPAYMENT'                   => ['s-ppy-xen2ybcovn56', 'https://api.heidelpay.com/v1/types/prepayment/s-ppy-xen2ybcovn56/'],
            'PRZELEWY24'                   => ['s-p24-xen2ybcovn56', 'https://api.heidelpay.com/v1/types/przelewy24/s-p24-xen2ybcovn56/'],
            'SEPA_DIRECT_DEBIT_GUARANTEED' => ['s-ddg-xen2ybcovn56', 'https://api.heidelpay.com/v1/types/direct-debit-guaranteed/s-ddg-xen2ybcovn56/'],
            'SEPA_DIRECT_DEBIT'            => ['s-sdd-xen2ybcovn56', 'https://api.heidelpay.com/v1/types/direct-debit/s-sdd-xen2ybcovn56/'],
            'SOFORT'                       => ['s-sft-xen2ybcovn56', 'https://api.heidelpay.com/v1/types/sofort/s-sft-xen2ybcovn56/'],
            'PIS'                          => ['s-pis-xen2ybcovn56', 'https://api.heidelpay.com/v1/types/pis/s-pis-xen2ybcovn56/'],
            'EPS'                          => ['s-eps-xen2ybcovn56', 'https://api.heidelpay.com/v1/types/eps/s-eps-xen2ybcovn56/'],
            'ALIPAY'                       => ['s-ali-xen2ybcovn56', 'https://api.heidelpay.com/v1/types/alipay/s-ali-xen2ybcovn56/'],
            'WECHATPAY'                    => ['s-wcp-xen2ybcovn56', 'https://api.heidelpay.com/v1/types/wechatpay/s-wcp-xen2ybcovn56/'],
            'INVOICE_FACTORING'            => ['s-ivf-xen2ybcovn56', 'https://api.heidelpay.com/v1/types/wechatpay/s-ivf-xen2ybcovn56/'],
            'HIRE_PURCHASE_DIRECT_DEBIT'   => ['s-hdd-xen2ybcovn56', 'https://api.heidelpay.com/v1/types/hire-purchase-direct-debit/s-hdd-xen2ybcovn56/']
        ];
    }

    /**
     * @return array
     */
    public function sendShouldCallSendOnHttpServiceDP(): array
    {
        return [
            HttpAdapterInterface::REQUEST_GET    => [HttpAdapterInterface::REQUEST_GET, '/my/get/uri', true],
            HttpAdapterInterface::REQUEST_POST   => [HttpAdapterInterface::REQUEST_POST, '/my/post/uri', false],
            HttpAdapterInterface::REQUEST_PUT    => [HttpAdapterInterface::REQUEST_PUT, '/my/put/uri', true],
            HttpAdapterInterface::REQUEST_DELETE => [HttpAdapterInterface::REQUEST_DELETE, '/my/delete/uri', true],
        ];
    }

    /**
     * @return array
     */
    public function fetchShouldCallFetchResourceDP(): array
    {
        $fetchPaymentCB          = static function ($payment) {
            return $payment instanceof Payment && $payment->getId() === 'myPaymentId';
        };
        $fetchPaymentByOrderIdCB = static function ($payment) {
            return $payment instanceof Payment && $payment->getOrderId() === 'myOrderId';
        };
        $fetchKeypairCB          = static function ($keypair) {
            return $keypair instanceof Keypair;
        };
        $fetchCustomerCB         = static function ($customer) {
            return $customer instanceof Customer && $customer->getId() === 'myCustomerId';
        };
        $fetchMetadataCB         = static function ($metadata) {
            return $metadata instanceof Metadata && $metadata->getId() === 's-mtd-1234';
        };
        $fetchBasketCB         = static function ($basket) {
            return $basket instanceof Basket && $basket->getId() === 'myBasketId';
        };

        // generate the asserting callback function for PaymentType fetch
        $getPaymentTypeCB = static function ($typeClass) {
            return static function ($type) use ($typeClass) {
                return $type instanceof $typeClass;
            };
        };

        return [
            'fetchPayment' => ['fetchPayment', [(new Payment())->setId('myPaymentId')], $fetchPaymentCB],
            'fetchPayment by id' => ['fetchPayment', ['myPaymentId'], $fetchPaymentCB],
            'fetchPayment by orderId' => ['fetchPaymentByOrderId', ['myOrderId'], $fetchPaymentByOrderIdCB],
            'fetchKeypair' => ['fetchKeypair', [], $fetchKeypairCB],
            'fetchCustomer' => ['fetchCustomer', ['myCustomerId'], $fetchCustomerCB],
            'fetchMetadata by obj' => ['fetchMetadata', [(new Metadata())->setId('s-mtd-1234')], $fetchMetadataCB],
            'fetchMetadata by id' => ['fetchMetadata', ['s-mtd-1234'], $fetchMetadataCB],
            'fetchBasket by id' => ['fetchBasket', ['myBasketId'], $fetchBasketCB],
            'fetchBasket by obj' => ['fetchBasket', [(new Basket())->setId('myBasketId')], $fetchBasketCB],
            'PaymentType Card sandbox' => ['fetchPaymentType', ['s-crd-12345678'], $getPaymentTypeCB(Card::class)],
            'PaymentType Giropay sandbox' => ['fetchPaymentType', ['s-gro-12345678'], $getPaymentTypeCB(Giropay::class)],
            'PaymentType Ideal sandbox' => ['fetchPaymentType', ['s-idl-12345678'], $getPaymentTypeCB(Ideal::class)],
            'PaymentType Invoice sandbox' => ['fetchPaymentType', ['s-ivc-12345678'], $getPaymentTypeCB(Invoice::class)],
            'PaymentType InvoiceGuaranteed sandbox' => ['fetchPaymentType', ['s-ivg-12345678'], $getPaymentTypeCB(InvoiceGuaranteed::class)],
            'PaymentType Paypal sandbox' => ['fetchPaymentType', ['s-ppl-12345678'], $getPaymentTypeCB(Paypal::class)],
            'PaymentType Prepayment sandbox' => ['fetchPaymentType', ['s-ppy-12345678'], $getPaymentTypeCB(Prepayment::class)],
            'PaymentType Przelewy24 sandbox' => ['fetchPaymentType', ['s-p24-12345678'], $getPaymentTypeCB(Przelewy24::class)],
            'PaymentType SepaDirectDebit sandbox' => ['fetchPaymentType', ['s-sdd-12345678'], $getPaymentTypeCB(SepaDirectDebit::class)],
            'PaymentType SepaDirectDebitGuaranteed sandbox' => ['fetchPaymentType', ['s-ddg-12345678'], $getPaymentTypeCB(SepaDirectDebitGuaranteed::class)],
            'PaymentType Sofort sandbox' => ['fetchPaymentType', ['s-sft-12345678'], $getPaymentTypeCB(Sofort::class)],
            'PaymentType PIS sandbox' => ['fetchPaymentType', ['s-pis-12345678'], $getPaymentTypeCB(PIS::class)],
            'PaymentType EPS sandbox' => ['fetchPaymentType', ['s-eps-12345678'], $getPaymentTypeCB(EPS::class)],
            'PaymentType Alipay sandbox' => ['fetchPaymentType', ['s-ali-12345678'], $getPaymentTypeCB(Alipay::class)],
            'PaymentType Wechatpay sandbox' => ['fetchPaymentType', ['s-wcp-12345678'], $getPaymentTypeCB(Wechatpay::class)],
            'PaymentType Invoice factoring sandbox' => ['fetchPaymentType', ['s-ivf-12345678'], $getPaymentTypeCB(InvoiceFactoring::class)],
            'PaymentType HirePurchaseDirectDebit sandbox' => ['fetchPaymentType', ['s-hdd-12345678'], $getPaymentTypeCB(HirePurchaseDirectDebit::class)],
            'PaymentType Card production' => ['fetchPaymentType', ['p-crd-12345678'], $getPaymentTypeCB(Card::class)],
            'PaymentType Giropay production' => ['fetchPaymentType', ['p-gro-12345678'], $getPaymentTypeCB(Giropay::class)],
            'PaymentType Ideal production' => ['fetchPaymentType', ['p-idl-12345678'], $getPaymentTypeCB(Ideal::class)],
            'PaymentType Invoice production' => ['fetchPaymentType', ['p-ivc-12345678'], $getPaymentTypeCB(Invoice::class)],
            'PaymentType InvoiceGuaranteed production' => ['fetchPaymentType', ['p-ivg-12345678'], $getPaymentTypeCB(InvoiceGuaranteed::class)],
            'PaymentType Paypal production' => ['fetchPaymentType', ['p-ppl-12345678'], $getPaymentTypeCB(Paypal::class)],
            'PaymentType Prepayment production' => ['fetchPaymentType', ['p-ppy-12345678'], $getPaymentTypeCB(Prepayment::class)],
            'PaymentType Przelewy24 production' => ['fetchPaymentType', ['p-p24-12345678'], $getPaymentTypeCB(Przelewy24::class)],
            'PaymentType SepaDirectDebit production' => ['fetchPaymentType', ['p-sdd-12345678'], $getPaymentTypeCB(SepaDirectDebit::class)],
            'PaymentType SepaDirectDebitGuaranteed production' => ['fetchPaymentType', ['p-ddg-12345678'], $getPaymentTypeCB(SepaDirectDebitGuaranteed::class)],
            'PaymentType Sofort production' => ['fetchPaymentType', ['p-sft-12345678'], $getPaymentTypeCB(Sofort::class)],
            'PaymentType EPS production' => ['fetchPaymentType', ['p-eps-12345678'], $getPaymentTypeCB(EPS::class)],
            'PaymentType Alipay production' => ['fetchPaymentType', ['p-ali-12345678'], $getPaymentTypeCB(Alipay::class)],
            'PaymentType Wechatpay production' => ['fetchPaymentType', ['p-wcp-12345678'], $getPaymentTypeCB(Wechatpay::class)],
            'PaymentType Invoice factoring production' => ['fetchPaymentType', ['p-ivf-12345678'], $getPaymentTypeCB(InvoiceFactoring::class)],
            'PaymentType HirePurchaseDirectDebit production' => ['fetchPaymentType', ['p-hdd-12345678'], $getPaymentTypeCB(HirePurchaseDirectDebit::class)]
        ];
    }

    //</editor-fold>
}
