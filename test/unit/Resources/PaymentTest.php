<?php
/**
 * This class defines unit tests to verify functionality of the Payment resource.
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
namespace heidelpayPHP\test\unit\Resources;

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Constants\PaymentState;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\Basket;
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\EmbeddedResources\Amount;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\Sofort;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\Resources\TransactionTypes\Shipment;
use heidelpayPHP\Services\ResourceService;
use heidelpayPHP\test\BaseUnitTest;
use PHPUnit\Framework\Exception;
use ReflectionException;
use RuntimeException;
use stdClass;

class PaymentTest extends BaseUnitTest
{
    /**
     * Verify getters and setters work properly.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function gettersAndSettersShouldWorkProperly()
    {
        $payment = (new Payment())->setParentResource(new Heidelpay('s-priv-1234'));
        $this->assertNull($payment->getRedirectUrl());
        $this->assertNull($payment->getCustomer());
        /** @noinspection UnnecessaryAssertionInspection */
        $this->assertInstanceOf(Amount::class, $payment->getAmount());

        $payment->setRedirectUrl('https://my-redirect-url.test');
        $this->assertEquals('https://my-redirect-url.test', $payment->getRedirectUrl());

        $authorize = new Authorization();
        $payment->setAuthorization($authorize);
        $this->assertSame($authorize, $payment->getAuthorization(true));
    }

    /**
     * Verify getAuthorization should try to fetch resource if lazy loading is off and the authorization is not null.
     *
     * @test
     *
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function getAuthorizationShouldFetchAuthorizeIfNotLazyAndAuthIsNotNull()
    {
        $payment = (new Payment())->setId('myPaymentId');
        $authorization = new Authorization();
        $payment->setAuthorization($authorization);

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)
            ->disableOriginalConstructor()->setMethods(['getResource'])->getMock();
        $resourceServiceMock->expects($this->once())->method('getResource')->with($authorization);

        /** @var ResourceService $resourceServiceMock */
        $heidelpayObj = (new Heidelpay('s-priv-123'))->setResourceService($resourceServiceMock);
        $payment->setParentResource($heidelpayObj);

        $payment->getAuthorization();
    }

    /**
     * Verify getAuthorization should try to fetch resource if lazy loading is off and the authorization is not null.
     *
     * @test
     *
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function getAuthorizationShouldNotFetchAuthorizeIfNotLazyAndAuthIsNull()
    {
        $payment = (new Payment())->setId('myPaymentId');

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)
            ->disableOriginalConstructor()->setMethods(['getResource'])->getMock();
        $resourceServiceMock->expects($this->never())->method('getResource');

        /** @var ResourceService $resourceServiceMock */
        $heidelpayObj = (new Heidelpay('s-priv-123'))->setResourceService($resourceServiceMock);
        $payment->setParentResource($heidelpayObj);

        $payment->getAuthorization();
    }

    /**
     * Verify Charge array is handled properly.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function chargesShouldBeHandledProperly()
    {
        $payment = new Payment();
        $this->assertIsEmptyArray($payment->getCharges());

        $charge1 = (new Charge())->setId('firstCharge');
        $charge2 = (new Charge())->setId('secondCharge');

        $subset[] = $charge1;
        $payment->addCharge($charge1);
        $this->assertArraySubset($subset, $payment->getCharges());

        $subset[] = $charge2;
        $payment->addCharge($charge2);
        $this->assertArraySubset($subset, $payment->getCharges());

        $this->assertSame($charge2, $payment->getCharge('secondCharge', true));
        $this->assertSame($charge1, $payment->getCharge('firstCharge', true));

        $this->assertSame($charge1, $payment->getChargeByIndex(0, true));
        $this->assertSame($charge2, $payment->getChargeByIndex(1, true));
    }

    /**
     * Verify getChargeById will fetch the Charge if lazy loading is off and the charge exists.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws ReflectionException
     * @throws HeidelpayApiException
     */
    public function getChargeByIdShouldFetchChargeIfItExistsAndLazyLoadingIsOff()
    {
        $payment = (new Payment())->setId('myPaymentId');
        $charge1 = (new Charge())->setId('firstCharge');
        $charge2 = (new Charge())->setId('secondCharge');

        $payment->addCharge($charge1);
        $payment->addCharge($charge2);

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)
            ->disableOriginalConstructor()->setMethods(['getResource'])->getMock();
        $resourceServiceMock->expects($this->exactly(2))
            ->method('getResource')
            ->withConsecutive([$charge1], [$charge2]);

        /** @var ResourceService $resourceServiceMock */
        $heidelpayObj = (new Heidelpay('s-priv-123'))->setResourceService($resourceServiceMock);
        $payment->setParentResource($heidelpayObj);

        $payment->getCharge('firstCharge');
        $payment->getCharge('secondCharge');
    }

    /**
     * Verify getCharge will fetch the Charge if lazy loading is off and the charge exists.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws ReflectionException
     * @throws HeidelpayApiException
     */
    public function getChargeShouldFetchChargeIfItExistsAndLazyLoadingIsOff()
    {
        $payment = (new Payment())->setId('myPaymentId');
        $charge1 = (new Charge())->setId('firstCharge');
        $charge2 = (new Charge())->setId('secondCharge');

        $payment->addCharge($charge1);
        $payment->addCharge($charge2);

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)
            ->disableOriginalConstructor()->setMethods(['getResource'])->getMock();
        $resourceServiceMock->expects($this->exactly(2))
            ->method('getResource')
            ->withConsecutive([$charge1], [$charge2]);

        /** @var ResourceService $resourceServiceMock */
        $heidelpayObj = (new Heidelpay('s-priv-123'))->setResourceService($resourceServiceMock);
        $payment->setParentResource($heidelpayObj);

        $payment->getChargeByIndex(0);
        $payment->getChargeByIndex(1);
    }

    /**
     * Verify getCharge and getChargeById will return null if the Charge does not exist.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function getChargeMethodsShouldReturnNullIfTheChargeIdUnknown()
    {
        $payment = (new Payment())->setId('myPaymentId');
        $charge1 = (new Charge())->setId('firstCharge');
        $charge2 = (new Charge())->setId('secondCharge');
        $payment->addCharge($charge1);
        $payment->addCharge($charge2);

        $this->assertSame($charge1, $payment->getCharge('firstCharge', true));
        $this->assertSame($charge2, $payment->getCharge('secondCharge', true));
        $this->assertNull($payment->getCharge('thirdCharge'));

        $this->assertSame($charge1, $payment->getChargeByIndex(0, true));
        $this->assertSame($charge2, $payment->getChargeByIndex(1, true));
        $this->assertNull($payment->getChargeByIndex(2));
    }

    /**
     * Verify setCustomer does nothing if the passed customer is empty.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function setCustomerShouldDoNothingIfTheCustomerIsEmpty()
    {
        $heidelpayObj = new Heidelpay('s-priv-123');
        $payment = (new Payment())->setParentResource($heidelpayObj);
        $customer = (new Customer('Max', 'Mustermann'))->setId('myCustomer');
        $payment->setCustomer($customer);

        $this->assertSame($customer, $payment->getCustomer());

        $payment->setCustomer(0);
        $this->assertSame($customer, $payment->getCustomer());

        $payment->setCustomer(null);
        $this->assertSame($customer, $payment->getCustomer());
    }

    /**
     * Verify setCustomer will try to fetch the customer if it is passed as string (i. e. id).
     *
     * @test
     *
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function setCustomerShouldFetchCustomerIfItIsPassedAsIdString()
    {
        $payment = (new Payment())->setId('myPaymentId');

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)
            ->disableOriginalConstructor()->setMethods(['fetchCustomer'])->getMock();
        $resourceServiceMock->expects($this->once())->method('fetchCustomer')->with('MyCustomerId');

        /** @var ResourceService $resourceServiceMock */
        $heidelpayObj = (new Heidelpay('s-priv-123'))->setResourceService($resourceServiceMock);
        $payment->setParentResource($heidelpayObj);

        $payment->setCustomer('MyCustomerId');
    }

    /**
     * Verify setCustomer will create the resource if it is passed as object without id.
     *
     * @test
     *
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function setCustomerShouldCreateCustomerIfItIsPassedAsObjectWithoutId()
    {
        $payment = (new Payment())->setId('myPaymentId');
        $customer = new Customer();

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)
            ->disableOriginalConstructor()->setMethods(['createCustomer'])->getMock();
        $resourceServiceMock->expects($this->once())->method('createCustomer')->with($customer);

        /** @var ResourceService $resourceServiceMock */
        $heidelpayObj = (new Heidelpay('s-priv-123'))->setResourceService($resourceServiceMock);
        $payment->setParentResource($heidelpayObj);

        $payment->setCustomer($customer);
    }

    /**
     * Verify setPaymentType will do nothing if the paymentType is empty.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function setPaymentTypeShouldDoNothingIfThePaymentTypeIsEmpty()
    {
        $heidelpayObj = new Heidelpay('s-priv-123');
        $payment = (new Payment())->setParentResource($heidelpayObj);
        $paymentType = (new Sofort())->setId('123');

        $payment->setPaymentType($paymentType);
        $this->assertSame($paymentType, $payment->getPaymentType());

        $payment->setPaymentType(0);
        $this->assertSame($paymentType, $payment->getPaymentType());

        $payment->setPaymentType(null);
        $this->assertSame($paymentType, $payment->getPaymentType());
    }

    /**
     * Verify setPaymentType will try to fetch the payment type if it is passed as string (i. e. id).
     *
     * @test
     *
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function setPaymentTypeShouldFetchResourceIfItIsPassedAsIdString()
    {
        $payment = (new Payment())->setId('myPaymentId');

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)
            ->disableOriginalConstructor()->setMethods(['fetchPaymentType'])->getMock();
        $resourceServiceMock->expects($this->once())->method('fetchPaymentType')->with('MyPaymentId');

        /** @var ResourceService $resourceServiceMock */
        $heidelpayObj = (new Heidelpay('s-priv-123'))->setResourceService($resourceServiceMock);
        $payment->setParentResource($heidelpayObj);

        $payment->setPaymentType('MyPaymentId');
    }

    /**
     * Verify setCustomer will create the resource if it is passed as object without id.
     *
     * @test
     *
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function setPaymentTypeShouldCreateResourceIfItIsPassedAsObjectWithoutId()
    {
        $payment = (new Payment())->setId('myPaymentId');
        $paymentType = new Sofort();

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)
            ->disableOriginalConstructor()->setMethods(['createPaymentType'])->getMock();
        $resourceServiceMock->expects($this->once())->method('createPaymentType')->with($paymentType);

        /** @var ResourceService $resourceServiceMock */
        $heidelpayObj = (new Heidelpay('s-priv-123'))->setResourceService($resourceServiceMock);
        $payment->setParentResource($heidelpayObj);

        $payment->setPaymentType($paymentType);
    }

    /**
     * Verify getCancellations will call getCancellations on all Charge and Authorization objects to fetch its refunds.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function getCancellationsShouldCollectAllCancellationsOfCorrespondingTransactions()
    {
        $payment = new Payment();
        $cancellation1 = (new Cancellation())->setId('cancellation1');
        $cancellation2 = (new Cancellation())->setId('cancellation2');
        $cancellation3 = (new Cancellation())->setId('cancellation3');
        $cancellation4 = (new Cancellation())->setId('cancellation4');

        $expectedCancellations = [];

        $this->assertArraySubset($expectedCancellations, $payment->getCancellations());

        $authorize = $this->getMockBuilder(Authorization::class)->setMethods(['getCancellations'])->getMock();
        $authorize->expects($this->exactly(4))->method('getCancellations')->willReturn([$cancellation1]);

        /** @var Authorization $authorize */
        $payment->setAuthorization($authorize);
        $expectedCancellations[] = $cancellation1;
        $this->assertArraySubset($expectedCancellations, $payment->getCancellations());

        $charge1 = $this->getMockBuilder(Charge::class)->setMethods(['getCancellations'])->getMock();
        $charge1->expects($this->exactly(3))->method('getCancellations')->willReturn([$cancellation2]);

        /** @var Charge $charge1 */
        $payment->addCharge($charge1);
        $expectedCancellations[] = $cancellation2;
        $this->assertArraySubset($expectedCancellations, $payment->getCancellations());

        $charge2 = $this->getMockBuilder(Charge::class)->setMethods(['getCancellations'])->getMock();
        $charge2->expects($this->exactly(2))->method('getCancellations')->willReturn([$cancellation3, $cancellation4]);

        /** @var Charge $charge2 */
        $payment->addCharge($charge2);
        $expectedCancellations[] = $cancellation3;
        $expectedCancellations[] = $cancellation4;
        $this->assertArraySubset($expectedCancellations, $payment->getCancellations());

        $charge3 = $this->getMockBuilder(Charge::class)->setMethods(['getCancellations'])->getMock();
        $charge3->expects($this->once())->method('getCancellations')->willReturn([]);

        /** @var Charge $charge3 */
        $payment->addCharge($charge3);
        $this->assertArraySubset($expectedCancellations, $payment->getCancellations());
    }

    /**
     * Verify getCancellation calls getCancellations and returns null if cancellation does not exist.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function getCancellationShouldCallGetCancellationsAndReturnNullIfNoCancellationExists()
    {
        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getCancellations'])->getMock();
        $paymentMock->expects($this->once())->method('getCancellations')->willReturn([]);

        /** @var Payment $paymentMock */
        $this->assertNull($paymentMock->getCancellation('123'));
    }

    /**
     * Verify getCancellation returns cancellation if it exists.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function getCancellationShouldReturnCancellationIfItExists()
    {
        $cancellation1 = (new Cancellation())->setId('cancellation1');
        $cancellation2 = (new Cancellation())->setId('cancellation2');
        $cancellation3 = (new Cancellation())->setId('cancellation3');
        $cancellations = [$cancellation1, $cancellation2, $cancellation3];

        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getCancellations'])->getMock();
        $paymentMock->expects($this->once())->method('getCancellations')->willReturn($cancellations);

        /** @var Payment $paymentMock */
        $this->assertSame($cancellation2, $paymentMock->getCancellation('cancellation2', true));
    }

    /**
     * Verify getCancellation fetches cancellation if it exists and lazy loading is false.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function getCancellationShouldReturnCancellationIfItExistsAndFetchItIfNotLazy()
    {
        $cancellation = (new Cancellation())->setId('cancellation123');

        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getCancellations'])->getMock();
        $paymentMock->expects($this->exactly(2))->method('getCancellations')->willReturn([$cancellation]);

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)
            ->disableOriginalConstructor()->setMethods(['getResource'])->getMock();
        $resourceServiceMock->expects($this->once())->method('getResource')->with($cancellation);

        /** @var ResourceService $resourceServiceMock */
        $heidelpayObj = (new Heidelpay('s-priv-123'))->setResourceService($resourceServiceMock);

        /** @var Payment $paymentMock */
        $paymentMock->setParentResource($heidelpayObj);

        $this->assertSame($cancellation, $paymentMock->getCancellation('cancellation123'));
        $this->assertNull($paymentMock->getCancellation('cancellation1234'));
    }

    /**
     * Verify Shipments are handled properly.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function shipmentsShouldBeHandledProperly()
    {
        $payment = new Payment();
        $this->assertIsEmptyArray($payment->getShipments());

        $shipment1 = (new Shipment())->setId('firstShipment');
        $shipment2 = (new Shipment())->setId('secondShipment');

        $subset[] = $shipment1;
        $payment->addShipment($shipment1);
        $this->assertArraySubset($subset, $payment->getShipments());

        $subset[] = $shipment2;
        $payment->addShipment($shipment2);
        $this->assertArraySubset($subset, $payment->getShipments());

        $this->assertSame($shipment2, $payment->getShipment('secondShipment', true));
        $this->assertSame($shipment1, $payment->getShipment('firstShipment', true));
    }

    /**
     * Verify getCancellation fetches cancellation if it exists and lazy loading is false.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function getShipmentByIdShouldReturnShipmentIfItExistsAndFetchItIfNotLazy()
    {
        $shipment = (new Shipment())->setId('shipment123');

        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getShipments'])->getMock();
        $paymentMock->expects($this->exactly(2))->method('getShipments')->willReturn([$shipment]);

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)
            ->disableOriginalConstructor()->setMethods(['getResource'])->getMock();
        $resourceServiceMock->expects($this->once())->method('getResource')->with($shipment);

        /** @var ResourceService $resourceServiceMock */
        $heidelpayObj = (new Heidelpay('s-priv-123'))->setResourceService($resourceServiceMock);

        /** @var Payment $paymentMock */
        $paymentMock->setParentResource($heidelpayObj);

        $this->assertSame($shipment, $paymentMock->getShipment('shipment123'));
        $this->assertNull($paymentMock->getShipment('shipment1234'));
    }

    /**
     * Verify the currency is fetched from the amount object.
     *
     * @test
     *
     * @throws Exception
     * @throws RuntimeException
     * @throws ReflectionException
     */
    public function getAndSetCurrencyShouldPropagateToTheAmountObject()
    {
        $amountMock = $this->getMockBuilder(Amount::class)->setMethods(['getCurrency', 'setCurrency'])->getMock();
        $amountMock->expects($this->once())->method('getCurrency')->willReturn('MyTestGetCurrency');
        $amountMock->expects($this->once())->method('setCurrency')->with('MyTestSetCurrency');

        $payment = new Payment();
        /** @var Amount $amountMock */
        $payment->setAmount($amountMock);

        $payment->setCurrency('MyTestSetCurrency');
        $this->assertEquals('MyTestGetCurrency', $payment->getCurrency());
    }

    //<editor-fold desc="Handle Response Tests">

    /**
     * Verify handleResponse will update stateId.
     *
     * @test
     * @dataProvider stateDataProvider
     *
     * @param integer $state
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function handleResponseShouldUpdateStateId($state)
    {
        $payment = new Payment();
        $this->assertEquals(PaymentState::STATE_PENDING, $payment->getState());

        $response = new stdClass();
        $response->state = new stdClass();
        $response->state->id = $state;
        $payment->handleResponse($response);
        $this->assertEquals($state, $payment->getState());
    }

    /**
     * Verify handleResponse updates payment id.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function handleResponseShouldUpdatePaymentId()
    {
        $payment = (new Payment())->setId('MyPaymentId');
        $this->assertEquals('MyPaymentId', $payment->getId());

        $response = new stdClass();
        $response->resources = new stdClass();
        $response->resources->paymentId = 'MyNewPaymentId';
        $payment->handleResponse($response);
        $this->assertEquals('MyNewPaymentId', $payment->getId());
    }

    /**
     * Verify handleResponse fetches Customer if it is not set.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws ReflectionException
     */
    public function handleResponseShouldFetchCustomerIfItIsNotSet()
    {
        $payment = (new Payment())->setId('myPaymentId');

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)
            ->disableOriginalConstructor()->setMethods(['fetchCustomer'])->getMock();
        $resourceServiceMock->expects($this->once())->method('fetchCustomer')->with('MyNewCustomerId');

        /** @var ResourceService $resourceServiceMock */
        $heidelpayObj = (new Heidelpay('s-priv-123'))->setResourceService($resourceServiceMock);
        $payment->setParentResource($heidelpayObj);

        $this->assertNull($payment->getCustomer());

        $response = new stdClass();
        $response->resources = new stdClass();
        $response->resources->customerId = 'MyNewCustomerId';
        $payment->handleResponse($response);
    }

    /**
     * Verify handleResponse updates customer if it set.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws ReflectionException
     */
    public function handleResponseShouldFetchAndUpdateCustomerIfItIsAlreadySet()
    {
        $payment = (new Payment())->setId('myPaymentId');
        $customer = (new Customer())->setId('customerId');

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)
            ->disableOriginalConstructor()->setMethods(['getResource'])->getMock();
        $resourceServiceMock->expects($this->once())->method('getResource')->with($customer);

        /** @var ResourceService $resourceServiceMock */
        $heidelpayObj = (new Heidelpay('s-priv-123'))->setResourceService($resourceServiceMock);
        $payment->setParentResource($heidelpayObj);
        $payment->setCustomer($customer);

        $response = new stdClass();
        $response->resources = new stdClass();
        $response->resources->customerId = 'customerId';
        $payment->handleResponse($response);
    }

    /**
     * Verify handleResponse updates paymentType.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws ReflectionException
     */
    public function handleResponseShouldFetchAndUpdatePaymentTypeIfTheIdIsSet()
    {
        $payment = (new Payment())->setId('myPaymentId');

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)
            ->disableOriginalConstructor()->setMethods(['fetchPaymentType'])->getMock();
        $resourceServiceMock->expects($this->once())->method('fetchPaymentType')->with('PaymentTypeId');

        /** @var ResourceService $resourceServiceMock */
        $heidelpayObj = (new Heidelpay('s-priv-123'))->setResourceService($resourceServiceMock);
        $payment->setParentResource($heidelpayObj);

        $response = new stdClass();
        $response->resources = new stdClass();
        $response->resources->typeId = 'PaymentTypeId';
        $payment->handleResponse($response);
    }

    /**
     * Verify handleResponse updates metadata.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws ReflectionException
     */
    public function handleResponseShouldFetchAndUpdateMetadataIfTheIdIsSet()
    {
        $payment = (new Payment())->setId('myPaymentId');

        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)
            ->disableOriginalConstructor()->setMethods(['fetchMetadata'])->getMock();
        $resourceServiceMock->expects($this->once())->method('fetchMetadata')->with('MetadataId');

        /** @var ResourceService $resourceServiceMock */
        $heidelpayObj = (new Heidelpay('s-priv-123'))->setResourceService($resourceServiceMock);
        $payment->setParentResource($heidelpayObj);

        $response = new stdClass();
        $response->resources = new stdClass();
        $response->resources->metadataId = 'MetadataId';
        $payment->handleResponse($response);
    }

    /**
     * Verify handleResponse does nothing if transactions is empty.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function handleResponseShouldUpdateChargeTransactions()
    {
        $payment = (new Payment())->setId('MyPaymentId');
        $this->assertIsEmptyArray($payment->getCharges());
        $this->assertIsEmptyArray($payment->getShipments());
        $this->assertIsEmptyArray($payment->getCancellations());
        $this->assertNull($payment->getAuthorization());

        $response = new stdClass();
        $response->transactions = [];
        $payment->handleResponse($response);

        $this->assertIsEmptyArray($payment->getCharges());
        $this->assertIsEmptyArray($payment->getShipments());
        $this->assertIsEmptyArray($payment->getCancellations());
        $this->assertNull($payment->getAuthorization());
    }

    /**
     * Verify handleResponse updates existing authorization from response.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function handleResponseShouldUpdateAuthorizationFromResponse()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $payment = (new Payment())->setParentResource($heidelpay)->setId('MyPaymentId');

        $authorization = (new Authorization(11.98, 'EUR'))->setId('s-aut-1');
        $this->assertEquals(11.98, $authorization->getAmount());

        $payment->setAuthorization($authorization);

        $authorizationData = new stdClass();
        $authorizationData->url = 'https://api-url.test/payments/MyPaymentId/authorize/s-aut-1';
        $authorizationData->amount = '10.321';
        $authorizationData->type = 'authorize';

        $response = new stdClass();
        $response->transactions = [$authorizationData];
        $payment->handleResponse($response);

        $authorization = $payment->getAuthorization(true);
        $this->assertInstanceOf(Authorization::class, $authorization);
        $this->assertEquals(10.321, $authorization->getAmount());
    }

    /**
     * Verify handleResponse adds authorization from response.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function handleResponseShouldAddAuthorizationFromResponse()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $payment = (new Payment())->setParentResource($heidelpay)->setId('MyPaymentId');
        $this->assertNull($payment->getAuthorization());

        $authorizationData = new stdClass();
        $authorizationData->url = 'https://api-url.test/payments/MyPaymentId/authorize/s-aut-1';
        $authorizationData->amount = '10.123';
        $authorizationData->type = 'authorize';

        $response = new stdClass();
        $response->transactions = [$authorizationData];
        $payment->handleResponse($response);

        $authorization = $payment->getAuthorization(true);
        $this->assertInstanceOf(Authorization::class, $authorization);
        $this->assertEquals('s-aut-1', $authorization->getId());
        $this->assertEquals(10.123, $authorization->getAmount());
        $this->assertSame($payment, $authorization->getPayment());
        $this->assertSame($payment, $authorization->getParentResource());
    }

    /**
     * Verify handleResponse updates existing charge from response.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function handleResponseShouldUpdateChargeFromResponseIfItExists()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $payment = (new Payment())->setParentResource($heidelpay)->setId('MyPaymentId');

        $charge1 = (new Charge(11.98, 'EUR'))->setId('s-chg-1');
        $charge2 = (new Charge(22.98, 'EUR'))->setId('s-chg-2');
        $this->assertEquals(22.98, $charge2->getAmount());

        $payment->addCharge($charge1)->addCharge($charge2);

        $chargeData = new stdClass();
        $chargeData->url = 'https://api-url.test/payments/MyPaymentId/charge/s-chg-2';
        $chargeData->amount = '11.111';
        $chargeData->type = 'charge';

        $response = new stdClass();
        $response->transactions = [$chargeData];
        $payment->handleResponse($response);

        $charge = $payment->getCharge('s-chg-2', true);
        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertSame($charge2, $charge);
        $this->assertEquals(11.111, $charge->getAmount());
    }

    /**
     * Verify handleResponse adds non existing charge from response.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function handleResponseShouldAddChargeFromResponseIfItDoesNotExists()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $payment = (new Payment())->setParentResource($heidelpay)->setId('MyPaymentId');

        $charge1 = (new Charge(11.98, 'EUR'))->setId('s-chg-1');
        $payment->addCharge($charge1);
        $this->assertCount(1, $payment->getCharges());
        $this->assertNull($payment->getCharge('s-chg-2'));

        $chargeData = new stdClass();
        $chargeData->url = 'https://api-url.test/payments/MyPaymentId/charge/s-chg-2';
        $chargeData->amount = '11.111';
        $chargeData->type = 'charge';

        $response = new stdClass();
        $response->transactions = [$chargeData];
        $payment->handleResponse($response);

        $charge = $payment->getCharge('s-chg-2', true);
        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertCount(2, $payment->getCharges());
        $this->assertEquals(11.111, $charge->getAmount());
    }

    /**
     * Verify handleResponse updates existing reversals from response.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function handleResponseShouldUpdateReversalFromResponseIfItExists()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $payment = (new Payment())->setParentResource($heidelpay)->setId('MyPaymentId');
        $authorize = (new Authorization(23.55, 'EUR'))->setId('s-aut-1');
        $payment->setAuthorization($authorize);
        $reversal1 = (new Cancellation(1.98))->setId('s-cnl-1');
        $reversal2 = (new Cancellation(2.98))->setId('s-cnl-2');
        $this->assertEquals(2.98, $reversal2->getAmount());
        $authorize->addCancellation($reversal1)->addCancellation($reversal2);

        $cancellation = new stdClass();
        $cancellation->url = 'https://api-url.test/payments/MyPaymentId/authorize/s-aut-1/cancel/s-cnl-2';
        $cancellation->amount = '11.111';
        $cancellation->type = 'cancel-authorize';

        $response = new stdClass();
        $response->transactions = [$cancellation];
        $payment->handleResponse($response);

        $authorization = $payment->getAuthorization(true);
        $cancellation = $authorization->getCancellation('s-cnl-2', true);
        $this->assertInstanceOf(Cancellation::class, $cancellation);
        $this->assertSame($reversal2, $cancellation);
        $this->assertEquals(11.111, $cancellation->getAmount());
    }

    /**
     * Verify handleResponse adds non existing reversal from response.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function handleResponseShouldAddReversalFromResponseIfItDoesNotExists()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $payment = (new Payment())->setParentResource($heidelpay)->setId('MyPaymentId');
        $authorize = (new Authorization(23.55, 'EUR'))->setId('s-aut-1');
        $payment->setAuthorization($authorize);
        $reversal1 = (new Cancellation(1.98))->setId('s-cnl-1');
        $authorize->addCancellation($reversal1);
        $this->assertNull($authorize->getCancellation('s-cnl-2'));
        $this->assertCount(1, $authorize->getCancellations());


        $cancellation = new stdClass();
        $cancellation->url = 'https://api-url.test/payments/MyPaymentId/authorize/s-aut-1/cancel/s-cnl-2';
        $cancellation->amount = '11.111';
        $cancellation->type = 'cancel-authorize';

        $response = new stdClass();
        $response->transactions = [$cancellation];
        $payment->handleResponse($response);

        $authorization = $payment->getAuthorization(true);
        $cancellation = $authorization->getCancellation('s-cnl-2', true);
        $this->assertInstanceOf(Cancellation::class, $cancellation);
        $this->assertEquals(11.111, $cancellation->getAmount());
        $this->assertCount(2, $authorize->getCancellations());
    }

    /**
     * Verify that handleResponse will throw an exception if the authorization to a reversal does not exist.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function handleResponseShouldThrowExceptionIfAnAuthorizeToAReversalDoesNotExist()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $payment = (new Payment())->setParentResource($heidelpay)->setId('MyPaymentId');

        $cancellation = new stdClass();
        $cancellation->url = 'https://api-url.test/payments/MyPaymentId/authorize/s-aut-1/cancel/s-cnl-2';
        $cancellation->amount = '11.111';
        $cancellation->type = 'cancel-authorize';

        $response = new stdClass();
        $response->transactions = [$cancellation];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The Authorization object can not be found.');
        $payment->handleResponse($response);
    }

    /**
     * Verify handleResponse updates existing refunds from response.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function handleResponseShouldUpdateRefundsFromResponseIfItExists()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $payment = (new Payment())->setParentResource($heidelpay)->setId('MyPaymentId');
        $charge = (new Charge(23.55, 'EUR'))->setId('s-chg-1');
        $payment->addCharge($charge);
        $refund1 = (new Cancellation(1.98))->setId('s-cnl-1');
        $refund2 = (new Cancellation(2.98))->setId('s-cnl-2');
        $this->assertEquals(2.98, $refund2->getAmount());
        $charge->addCancellation($refund1)->addCancellation($refund2);

        $cancellation = new stdClass();
        $cancellation->url = 'https://api-url.test/payments/MyPaymentId/charge/s-chg-1/cancel/s-cnl-2';
        $cancellation->amount = '11.111';
        $cancellation->type = 'cancel-charge';

        $response = new stdClass();
        $response->transactions = [$cancellation];
        $payment->handleResponse($response);

        $fetchedCharge = $payment->getCharge('s-chg-1', true);
        $cancellation = $fetchedCharge->getCancellation('s-cnl-2', true);
        $this->assertInstanceOf(Cancellation::class, $cancellation);
        $this->assertSame($refund2, $cancellation);
        $this->assertEquals(11.111, $cancellation->getAmount());
    }

    /**
     * Verify handleResponse adds non existing refund from response.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function handleResponseShouldAddRefundFromResponseIfItDoesNotExists()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $payment = (new Payment())->setParentResource($heidelpay)->setId('MyPaymentId');
        $charge = (new Charge(23.55, 'EUR'))->setId('s-chg-1');
        $payment->addCharge($charge);
        $reversal1 = (new Cancellation(1.98))->setId('s-cnl-1');
        $charge->addCancellation($reversal1);
        $this->assertNull($charge->getCancellation('s-cnl-2'));
        $this->assertCount(1, $charge->getCancellations());


        $cancellation = new stdClass();
        $cancellation->url = 'https://api-url.test/payments/MyPaymentId/charge/s-chg-1/cancel/s-cnl-2';
        $cancellation->amount = '11.111';
        $cancellation->type = 'cancel-charge';

        $response = new stdClass();
        $response->transactions = [$cancellation];
        $payment->handleResponse($response);

        $fetchedCharge = $payment->getCharge('s-chg-1', true);
        $cancellation = $fetchedCharge->getCancellation('s-cnl-2', true);
        $this->assertInstanceOf(Cancellation::class, $cancellation);
        $this->assertEquals(11.111, $cancellation->getAmount());
        $this->assertCount(2, $charge->getCancellations());
    }

    /**
     * Verify that handleResponse will throw an exception if the charge to a refund does not exist.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function handleResponseShouldThrowExceptionIfAChargeToARefundDoesNotExist()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $payment = (new Payment())->setParentResource($heidelpay)->setId('MyPaymentId');

        $cancellation = new stdClass();
        $cancellation->url = 'https://api-url.test/payments/MyPaymentId/charge/s-chg-1/cancel/s-cnl-2';
        $cancellation->amount = '11.111';
        $cancellation->type = 'cancel-charge';

        $response = new stdClass();
        $response->transactions = [$cancellation];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The Charge object can not be found.');
        $payment->handleResponse($response);
    }

    /**
     * Verify handleResponse updates existing refunds from response.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function handleResponseShouldUpdateShipmentFromResponseIfItExists()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $payment = (new Payment())->setParentResource($heidelpay)->setId('MyPaymentId');
        $shipment = (new Shipment())->setAmount('1.23')->setId('s-shp-1');
        $this->assertEquals('1.23', $shipment->getAmount());
        $payment->addShipment($shipment);

        $cancellation = new stdClass();
        $cancellation->url = 'https://api-url.test/payments/MyPaymentId/shipment/s-shp-1';
        $cancellation->amount = '11.111';
        $cancellation->type = 'shipment';

        $response = new stdClass();
        $response->transactions = [$cancellation];
        $payment->handleResponse($response);

        $fetchedShipment = $payment->getShipment('s-shp-1', true);
        $this->assertInstanceOf(Shipment::class, $fetchedShipment);
        $this->assertSame($shipment, $fetchedShipment);
        $this->assertEquals(11.111, $fetchedShipment->getAmount());
    }

    /**
     * Verify handleResponse adds non existing refund from response.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function handleResponseShouldAddShipmentFromResponseIfItDoesNotExists()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $payment = (new Payment())->setParentResource($heidelpay)->setId('MyPaymentId');
        $this->assertNull($payment->getShipment('s-shp-1'));
        $this->assertCount(0, $payment->getShipments());

        $cancellation = new stdClass();
        $cancellation->url = 'https://api-url.test/payments/MyPaymentId/shipment/s-shp-1';
        $cancellation->amount = '11.111';
        $cancellation->type = 'shipment';

        $response = new stdClass();
        $response->transactions = [$cancellation];
        $payment->handleResponse($response);

        $fetchedShipment = $payment->getShipment('s-shp-1', true);
        $this->assertInstanceOf(Shipment::class, $fetchedShipment);
        $this->assertEquals(11.111, $fetchedShipment->getAmount());
        $this->assertCount(1, $payment->getShipments());
    }

    //</editor-fold>

    //<editor-fold desc="Cancel">

    /**
     * Verify payment:cancel calls cancelAllCharges and cancelAuthorization and returns first charge cancellation
     * object.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function cancelShouldCallCancelAllChargesAndCancelAuthorizationAndReturnFirstChargeCancellationObject()
    {
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->setMethods(['cancelAllCharges', 'cancelAuthorization'])->getMock();
        $cancellation = new Cancellation(1.0);
        $exception1 = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CHARGED_BACK);
        $exception2 = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CHARGED_BACK);
        $exception3 = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CHARGED_BACK);
        $paymentMock->expects($this->once())->method('cancelAllCharges')
            ->willReturn([[$cancellation], [$exception1, $exception2]]);
        $paymentMock->expects($this->once())->method('cancelAuthorization')->willReturn([[], [$exception3]]);

        /** @var Payment $paymentMock */
        $this->assertSame($cancellation, $paymentMock->cancel());
    }

    /**
     * Verify payment:cancel calls cancelAllCharges and cancelAuthorization and returns authorize cancellation object if
     * no charge cancellation object exist.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function cancelShouldReturnAuthorizationCancellationObjectIfNoChargeCancellationsExist()
    {
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->setMethods(['cancelAllCharges', 'cancelAuthorization'])->getMock();
        $cancellation = new Cancellation(2.0);
        $exception1 = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CHARGED_BACK);
        $exception2 = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CHARGED_BACK);
        $exception3 = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CHARGED_BACK);
        $paymentMock->expects($this->once())->method('cancelAllCharges')
            ->willReturn([[], [$exception1, $exception2]]);
        $paymentMock->expects($this->once())->method('cancelAuthorization')
            ->willReturn([[$cancellation], [$exception3]]);

        /** @var Payment $paymentMock */
        $this->assertSame($cancellation, $paymentMock->cancel());
    }

    /**
     * Verify payment:cancel throws first charge exception if all charges and auth have already been cancelled.
     *
     * @test
     *
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function cancelShouldThrowFirstChargeAlreadyCancelledExceptionIfNoCancellationTookPlace()
    {
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->setMethods(['cancelAllCharges', 'cancelAuthorization'])->getMock();
        $exception1 = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CHARGED_BACK);
        $exception2 = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CHARGED_BACK);
        $exception3 = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CHARGED_BACK);
        $paymentMock->expects($this->once())->method('cancelAllCharges')->willReturn([[], [$exception1, $exception2]]);
        $paymentMock->expects($this->once())->method('cancelAuthorization')->willReturn([[], [$exception3]]);

        try {
            /** @var Payment $paymentMock */
            $paymentMock->cancel();
            $this->assertFalse(true, 'The expected exception has not been thrown.');
        } catch (HeidelpayApiException $e) {
            $this->assertSame($exception1, $e);
        }
    }

    /**
     * Verify payment:cancel throws auth exception if no charges existed and the authorization was already cancelled.
     *
     * @test
     *
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function cancelShouldThrowAuthAlreadyCancelledExceptionIfNoCancellationTookPlaceAndNoChargeExceptionExists()
    {
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->setMethods(['cancelAllCharges', 'cancelAuthorization'])->getMock();
        $exception = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CHARGED_BACK);
        $paymentMock->expects($this->once())->method('cancelAllCharges')->willReturn([[], []]);
        $paymentMock->expects($this->once())->method('cancelAuthorization')->willReturn([[], [$exception]]);

        try {
            /** @var Payment $paymentMock */
            $paymentMock->cancel();
            $this->assertFalse(true, 'The expected exception has not been thrown.');
        } catch (HeidelpayApiException $e) {
            $this->assertSame($exception, $e);
        }
    }

    /**
     * Verify payment:cancel throws Exception if no cancellation and no auth existed to be cancelled.
     *
     * @test
     *
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function cancelShouldThrowExceptionIfNoTransactionExistsToBeCancelled()
    {
        $paymentMock = $this->getMockBuilder(Payment::class)
            ->setMethods(['cancelAllCharges', 'cancelAuthorization'])->getMock();
        $paymentMock->expects($this->once())->method('cancelAllCharges')->willReturn([[], []]);
        $paymentMock->expects($this->once())->method('cancelAuthorization')->willReturn([[], []]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This Payment could not be cancelled.');

        /** @var Payment $paymentMock */
        $paymentMock->cancel();
    }

    /**
     * Verify cancel all charges will call cancel on each existing charge of the payment and will return a list of
     * cancels and exceptions.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function cancelAllChargesShouldCallCancelOnAllChargesAndReturnCancelsAndExceptions()
    {
        $cancellation1 = new Cancellation(1.0);
        $cancellation2 = new Cancellation(2.0);
        $cancellation3 = new Cancellation(3.0);
        $exception1 = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CHARGED_BACK);
        $exception2 = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CHARGED_BACK);

        $chargeMock1 = $this->getMockBuilder(Charge::class)->setMethods(['cancel'])->getMock();
        $chargeMock1->expects($this->once())->method('cancel')->willReturn($cancellation1);

        $chargeMock2 = $this->getMockBuilder(Charge::class)->setMethods(['cancel'])->getMock();
        $chargeMock2->expects($this->once())->method('cancel')->willThrowException($exception1);

        $chargeMock3 = $this->getMockBuilder(Charge::class)->setMethods(['cancel'])->getMock();
        $chargeMock3->expects($this->once())->method('cancel')->willReturn($cancellation2);

        $chargeMock4 = $this->getMockBuilder(Charge::class)->setMethods(['cancel'])->getMock();
        $chargeMock4->expects($this->once())->method('cancel')->willThrowException($exception2);

        $chargeMock5 = $this->getMockBuilder(Charge::class)->setMethods(['cancel'])->getMock();
        $chargeMock5->expects($this->once())->method('cancel')->willReturn($cancellation3);

        /**
         * @var Charge $chargeMock1
         * @var Charge $chargeMock2
         * @var Charge $chargeMock3
         * @var Charge $chargeMock4
         * @var Charge $chargeMock5
         */
        $payment = new Payment();
        $payment->addCharge($chargeMock1)
                ->addCharge($chargeMock2)
                ->addCharge($chargeMock3)
                ->addCharge($chargeMock4)
                ->addCharge($chargeMock5);

        list($cancellations, $exceptions) = $payment->cancelAllCharges();
        $this->assertArraySubset([$cancellation1, $cancellation2, $cancellation3], $cancellations);
        $this->assertArraySubset([$exception1, $exception2], $exceptions);
    }

    /**
     * Verify cancelAllCharges will throw any erxception with Code different to
     * ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CANCELED.
     *
     * @test
     *
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function cancelAllChargesShouldThrowChargeCancelExceptionsOtherThanAlreadyCharged()
    {
        $ex1 = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CHARGED_BACK);
        $ex2 = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_CHARGED_AMOUNT_HIGHER_THAN_EXPECTED);

        $chargeMock1 = $this->getMockBuilder(Charge::class)->setMethods(['cancel'])->getMock();
        $chargeMock1->expects($this->once())->method('cancel')->willThrowException($ex1);

        $chargeMock2 = $this->getMockBuilder(Charge::class)->setMethods(['cancel'])->getMock();
        $chargeMock2->expects($this->once())->method('cancel')->willThrowException($ex2);

        /**
         * @var Charge $chargeMock1
         * @var Charge $chargeMock2
         */
        $payment = (new Payment())->addCharge($chargeMock1)->addCharge($chargeMock2);

        try {
            $payment->cancelAllCharges();
            $this->assertFalse(true, 'The expected exception has not been thrown.');
        } catch (HeidelpayApiException $e) {
            $this->assertSame($ex2, $e);
        }
    }

    /**
     * Verify cancelAuthorization will call cancel on the authorization and will return a list of cancels.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function cancelAuthorizationShouldCallCancelOnTheAuthorizationAndReturnCancels()
    {
        $cancellation = new Cancellation(1.0);
        $authorizationMock = $this->getMockBuilder(Authorization::class)->setMethods(['cancel'])->getMock();
        $authorizationMock->expects($this->once())->method('cancel')->willReturn($cancellation);

        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getAuthorization'])->getMock();
        $paymentMock->expects($this->once())->method('getAuthorization')->willReturn($authorizationMock);

        /**
         * @var Authorization $authorizationMock
         * @var Payment       $paymentMock
         */
        $paymentMock->setAuthorization($authorizationMock);
        list($cancellations, $exceptions) = $paymentMock->cancelAuthorization();
        $this->assertArraySubset([$cancellation], $cancellations);
        $this->assertIsEmptyArray($exceptions);
    }

    /**
     * Verify cancelAuthorization will call cancel on the authorization and will return a list of exceptions.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function cancelAuthorizationShouldCallCancelOnTheAuthorizationAndReturnExceptions()
    {
        $exception = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_AUTHORIZE_ALREADY_CANCELLED);

        $authorizationMock = $this->getMockBuilder(Authorization::class)->setMethods(['cancel'])->getMock();
        $authorizationMock->expects($this->once())->method('cancel')->willThrowException($exception);

        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getAuthorization'])->getMock();
        $paymentMock->expects($this->once())->method('getAuthorization')->willReturn($authorizationMock);

        /**
         * @var Authorization $authorizationMock
         * @var Payment       $paymentMock
         */
        $paymentMock->setAuthorization($authorizationMock);
        list($cancellations, $exceptions) = $paymentMock->cancelAuthorization();

        $this->assertIsEmptyArray($cancellations);
        $this->assertArraySubset([$exception], $exceptions);
    }

    /**
     * Verify cancelAuthorization will throw any exception with Code different to
     * ApiResponseCodes::API_ERROR_AUTHORIZATION_ALREADY_CANCELED.
     *
     * @test
     *
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function cancelAllChargesShouldThrowAuthorizationCancelExceptionsOtherThanAlreadyCharged()
    {
        $exception = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_CHARGED_AMOUNT_HIGHER_THAN_EXPECTED);

        $authorizationMock = $this->getMockBuilder(Authorization::class)->setMethods(['cancel'])->getMock();
        $authorizationMock->expects($this->once())->method('cancel')->willThrowException($exception);

        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getAuthorization'])->getMock();
        $paymentMock->expects($this->once())->method('getAuthorization')->willReturn($authorizationMock);

        /**
         * @var Authorization $authorizationMock
         * @var Payment       $paymentMock
         */
        $paymentMock->setAuthorization($authorizationMock);

        try {
            $paymentMock->cancelAuthorization();
            $this->assertFalse(true, 'The expected exception has not been thrown.');
        } catch (HeidelpayApiException $e) {
            $this->assertSame($exception, $e);
        }
    }

    //</editor-fold>

    /**
     * Verify charge will call chargePayment on heidelpay object.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function chargeMethodShouldPropagateToHeidelpayChargePaymentMethod()
    {
        $payment = new Payment();
        $heidelpayMock = $this->getMockBuilder(Heidelpay::class)->disableOriginalConstructor()
            ->setMethods(['chargePayment'])->getMock();
        $heidelpayMock->expects($this->exactly(3))->method('chargePayment')
            ->withConsecutive(
                [$payment, null, null],
                [$payment, 1.1, null],
                [$payment, 2.2, 'MyCurrency']
            )->willReturn(new Charge());

        /** @var Heidelpay $heidelpayMock */
        $payment->setParentResource($heidelpayMock);

        $payment->charge();
        $payment->charge(1.1);
        $payment->charge(2.2, 'MyCurrency');
    }

    /**
     * Verify ship will call ship method on heidelpay object.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function shipMethodShouldPropagateToHeidelpayChargePaymentMethod()
    {
        $payment = new Payment();
        $heidelpayMock = $this->getMockBuilder(Heidelpay::class)->disableOriginalConstructor()
            ->setMethods(['ship'])->getMock();
        $heidelpayMock->expects($this->once())->method('ship')->willReturn(new Shipment());

        /** @var Heidelpay $heidelpayMock */
        $payment->setParentResource($heidelpayMock);

        $payment->ship();
    }

    /**
     * Verify setMetadata will set parent resource and call create with metadata object.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws ReflectionException
     * @throws HeidelpayApiException
     */
    public function setMetaDataShouldSetParentResourceAndCreateMetaDataObject()
    {
        $metadata = (new Metadata())->addMetadata('myData', 'myValue');

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->once())->method('create')->with($metadata);

        /** @var ResourceService $resourceSrvMock */
        $heidelpay = (new Heidelpay('s-priv-1234'))->setResourceService($resourceSrvMock);
        $payment = new Payment($heidelpay);

        try {
            $metadata->getParentResource();
            $this->assertTrue(false, 'This exception should have been thrown!');
        } catch (RuntimeException $e) {
            $this->assertInstanceOf(RuntimeException::class, $e);
            $this->assertEquals('Parent resource reference is not set!', $e->getMessage());
        }

        $payment->setMetadata($metadata);
        $this->assertSame($heidelpay, $metadata->getParentResource());
    }

    /**
     * Verify set Basket will call create if the given basket object does not exist yet.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function setBasketShouldCallCreateIfTheGivenBasketObjectDoesNotExistYet()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setConstructorArgs([$heidelpay])->setMethods(['create'])->getMock();

        /** @var ResourceService $resourceSrvMock */
        $heidelpay->setResourceService($resourceSrvMock);

        $basket = new Basket();
        $resourceSrvMock->expects($this->once())->method('create')->with(
            $this->callback(
                static function ($object) use ($basket, $heidelpay) {
                    /** @var Basket $object */
                    return $object === $basket && $object->getParentResource() === $heidelpay;
                })
        );

        $payment = new Payment($heidelpay);
        $payment->setBasket($basket);
    }

    /**
     * Verify setBasket won't call resource service when the basket is null.
     *
     * @test
     *
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function setBasketWontCallResourceServiceWhenBasketIsNull()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setConstructorArgs([$heidelpay])->setMethods(['create'])->getMock();

        /** @var ResourceService $resourceSrvMock */
        $heidelpay->setResourceService($resourceSrvMock);
        $resourceSrvMock->expects($this->once())->method('create');

        // set basket first to prove the setter works both times
        $basket = new Basket();
        $payment = new Payment($heidelpay);
        $payment->setBasket($basket);
        $this->assertSame($basket, $payment->getBasket());

        $payment->setBasket(null);
        $this->assertNull($payment->getBasket());
    }

    /**
     * Verify updateResponseResources will fetch the basketId in response if it is set.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function updateResponseResourcesShouldFetchBasketIdIfItIsSetInResponse()
    {
        $heidelpayMock = $this->getMockBuilder(Heidelpay::class)->disableOriginalConstructor()
            ->setMethods(['fetchBasket'])->getMock();

        $basket = new Basket();
        $heidelpayMock->expects($this->once())->method('fetchBasket')->with('myResourcesBasketId')->willReturn($basket);

        $payment  = new Payment($heidelpayMock);
        $response = new stdClass();
        $payment->handleResponse($response);
        $this->assertNull($payment->getBasket());

        $response->resources = new stdClass();
        $response->resources->basketId = 'myResourcesBasketId';
        $payment->handleResponse($response);
    }

    /**
     * Verify a payment is fetched by orderId if the id is not set.
     *
     * @test
     *
     * @throws RuntimeException
     */
    public function paymentShouldBeFetchedByOrderIdIfIdIsNotSet()
    {
        $orderId = str_replace(' ', '', microtime());
        $payment = (new Payment())->setOrderId($orderId)->setParentResource(new Heidelpay('s-priv-123'));
        $lastElement      = explode('/', rtrim($payment->getUri(), '/'));
        $this->assertEquals($orderId, end($lastElement));
    }

    //<editor-fold desc="Data Providers">

    /**
     * Provides the different payment states.
     *
     * @return array
     */
    public function stateDataProvider(): array
    {
        return [
            PaymentState::STATE_NAME_PENDING        => [PaymentState::STATE_PENDING],
            PaymentState::STATE_NAME_COMPLETED      => [PaymentState::STATE_COMPLETED],
            PaymentState::STATE_NAME_CANCELED       => [PaymentState::STATE_CANCELED],
            PaymentState::STATE_NAME_PARTLY         => [PaymentState::STATE_PARTLY],
            PaymentState::STATE_NAME_PAYMENT_REVIEW => [PaymentState::STATE_PAYMENT_REVIEW],
            PaymentState::STATE_NAME_CHARGEBACK     => [PaymentState::STATE_CHARGEBACK]
        ];
    }

    //</editor-fold>
}
