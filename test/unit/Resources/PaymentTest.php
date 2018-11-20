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
 * @package  heidelpay/mgw_sdk/test/unit
 */
namespace heidelpay\MgwPhpSdk\test\unit\Resources;

use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Resources\Customer;
use heidelpay\MgwPhpSdk\Resources\EmbeddedResources\Amount;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Sofort;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Cancellation;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Shipment;
use heidelpay\MgwPhpSdk\Services\ResourceService;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    /**
     * Verify getters and setters work properly.
     *
     * @test
     *
     * @throws ExpectationFailedException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function getAuthorizationShouldFetchAuthorizeIfNotLazyAndAuthIsNotNull()
    {
        $payment = (new Payment())->setId('myPaymentId');
        $authorization = (new Authorization())->setParentResource($payment);
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
     * @throws Exception
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws AssertionFailedError
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws \RuntimeException
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

        $this->assertSame($charge2, $payment->getChargeById('secondCharge', true));
        $this->assertSame($charge1, $payment->getChargeById('firstCharge', true));

        $this->assertSame($charge1, $payment->getCharge(0, true));
        $this->assertSame($charge2, $payment->getCharge(1, true));
    }

    /**
     * Verify getChargeById will fetch the Charge if lazy loading is off and the charge exists.
     *
     * @test
     *
     * @throws Exception
     * @throws \RuntimeException
     * @throws \ReflectionException
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

        $payment->getChargeById('firstCharge');
        $payment->getChargeById('secondCharge');
    }

    /**
     * Verify getCharge will fetch the Charge if lazy loading is off and the charge exists.
     *
     * @test
     *
     * @throws Exception
     * @throws \RuntimeException
     * @throws \ReflectionException
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

        $payment->getCharge(0);
        $payment->getCharge(1);
    }

    /**
     * Verify getCharge and getChargeById will return null if the Charge does not exist.
     *
     * @test
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws \RuntimeException
     */
    public function getChargeMethodsShouldReturnNullIfTheChargeIdUnknown()
    {
        $payment = (new Payment())->setId('myPaymentId');
        $charge1 = (new Charge())->setId('firstCharge');
        $charge2 = (new Charge())->setId('secondCharge');
        $payment->addCharge($charge1);
        $payment->addCharge($charge2);

        $this->assertSame($charge1, $payment->getChargeById('firstCharge', true));
        $this->assertSame($charge2, $payment->getChargeById('secondCharge', true));
        $this->assertNull($payment->getChargeById('thirdCharge'));

        $this->assertSame($charge1, $payment->getCharge(0, true));
        $this->assertSame($charge2, $payment->getCharge(1, true));
        $this->assertNull($payment->getCharge(2));
    }

    /**
     * Verify setCustomer does nothing if the passed customer is empty.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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
     * @throws AssertionFailedError
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws HeidelpayApiException
     * @throws \RuntimeException
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

        $this->assertSame($shipment2, $payment->getShipmentById('secondShipment', true));
        $this->assertSame($shipment1, $payment->getShipmentById('firstShipment', true));
    }

    /**
     * Verify getCancellation fetches cancellation if it exists and lazy loading is false.
     *
     * @test
     *
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
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

        $this->assertSame($shipment, $paymentMock->getShipmentById('shipment123'));
        $this->assertNull($paymentMock->getShipmentById('shipment1234'));
    }

    //<editor-fold desc="Helpers">

    /**
     * This performs assertions to verify the tested value is an empty array.
     *
     * @param mixed $value
     *
     * @throws AssertionFailedError
     * @throws Exception
     * @throws ExpectationFailedException
     */
    public function assertIsEmptyArray($value)
    {
        $this->assertInternalType('array', $value);
        $this->assertEmpty($value);
    }

    //</editor-fold>
}
