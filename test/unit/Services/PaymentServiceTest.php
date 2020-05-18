<?php
/**
 * This class defines unit tests to verify functionality of the payment service.
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

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Constants\TransactionTypes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Interfaces\CancelServiceInterface;
use heidelpayPHP\Interfaces\ResourceServiceInterface;
use heidelpayPHP\Resources\Basket;
use heidelpayPHP\Resources\Customer;
use heidelpayPHP\Resources\InstalmentPlans;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\HirePurchaseDirectDebit;
use heidelpayPHP\Resources\PaymentTypes\Paypage;
use heidelpayPHP\Resources\PaymentTypes\Paypal;
use heidelpayPHP\Resources\PaymentTypes\SepaDirectDebit;
use heidelpayPHP\Resources\PaymentTypes\Sofort;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\Resources\TransactionTypes\Payout;
use heidelpayPHP\Resources\TransactionTypes\Shipment;
use heidelpayPHP\Services\CancelService;
use heidelpayPHP\Services\PaymentService;
use heidelpayPHP\Services\ResourceService;
use heidelpayPHP\test\BasePaymentTest;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;
use RuntimeException;
use function in_array;

class PaymentServiceTest extends BasePaymentTest
{
    //<editor-fold desc="General">

    /**
     * Verify setters and getters work properly.
     *
     * @test
     */
    public function gettersAndSettersShouldWorkProperly()
    {
        $heidelpay      = new Heidelpay('s-priv-123');
        /** @var PaymentService $paymentService */
        $paymentService = $heidelpay->getPaymentService();
        $this->assertSame($heidelpay, $paymentService->getHeidelpay());
        $this->assertSame($heidelpay->getResourceService(), $paymentService->getResourceService());

        $heidelpay2       = new Heidelpay('s-priv-1234');
        $paymentService->setHeidelpay($heidelpay2);
        $this->assertSame($heidelpay2, $paymentService->getHeidelpay());
    }

    //</editor-fold>

    //<editor-fold desc="Authorize">

    /**
     * Verify authorizeWithPayment calls create for a new authorization using the passed values.
     *
     * @test
     *
     * @param $card3ds
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     * @dataProvider card3dsDataProvider
     */
    public function authorizeShouldCreateNewAuthorizationAndPayment($card3ds)
    {
        $customer  = (new Customer())->setId('myCustomerId');
        $metadata  = (new Metadata())->setId('myMetadataId');
        $basket    = (new Basket())->setId('myBasketId');

        /** @var ResourceService|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()->setMethods(['createResource'])->getMock();
        $paymentSrv  = (new Heidelpay('s-priv-123'))->setResourceService($resourceSrvMock)->getPaymentService();
        $resourceSrvMock->expects($this->once())->method('createResource')
            ->with($this->callback(static function ($authorize) use ($customer, $metadata, $basket, $card3ds) {
                /** @var Authorization $authorize */
                $newPayment = $authorize->getPayment();
                return $authorize instanceof Authorization &&
                    $authorize->getAmount() === 1.234 &&
                    $authorize->getCurrency() === 'myCurrency' &&
                    $authorize->getOrderId() === 'myId' &&
                    $authorize->getReturnUrl() === 'myUrl' &&
                    $authorize->isCard3ds() === $card3ds &&
                    $newPayment instanceof Payment &&
                    $newPayment->getMetadata() === $metadata &&
                    $newPayment->getCustomer() === $customer &&
                    $newPayment->getBasket() === $basket &&
                    $newPayment->getAuthorization() === $authorize;
            }));

        $type = (new PayPal())->setId('typeId');
        $paymentSrv->authorize(1.234, 'myCurrency', $type, 'myUrl', $customer, 'myId', $metadata, $basket, $card3ds);
    }

    //</editor-fold>

    //<editor-fold desc="Charge">

    /**
     * Verify charge method calls create with a charge object on resource service.
     *
     * @test
     *
     * @param $card3ds
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     * @dataProvider card3dsDataProvider
     */
    public function chargeShouldCreateNewPaymentAndCharge($card3ds)
    {
        $customer    = (new Customer())->setId('myCustomerId');
        $heidelpay   = new Heidelpay('s-priv-123');
        $paymentType = (new Sofort())->setId('myPaymentTypeId');
        $metadata    = (new Metadata())->setId('myMetadataId');
        $basket      = (new Basket())->setId('myBasketId');

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()->setMethods(['createResource'])->getMock();
        $resourceSrvMock->expects($this->once())->method('createResource')
            ->with($this->callback(static function ($charge) use ($customer, $paymentType, $basket, $card3ds) {
                /** @var Charge $charge */
                $newPayment = $charge->getPayment();
                return $charge instanceof Charge &&
                    $charge->getAmount() === 1.234 &&
                    $charge->getCurrency() === 'myCurrency' &&
                    $charge->getOrderId() === 'myId' &&
                    $charge->getReturnUrl() === 'myUrl' &&
                    $charge->isCard3ds() === $card3ds &&
                    $newPayment instanceof Payment &&
                    $newPayment->getCustomer() === $customer &&
                    $newPayment->getPaymentType() === $paymentType &&
                    $newPayment->getBasket() === $basket &&
                    in_array($charge, $newPayment->getCharges(), true);
            }));

        /** @var ResourceService $resourceSrvMock */
        $paymentSrv     = $heidelpay->setResourceService($resourceSrvMock)->getPaymentService();
        $returnedCharge = $paymentSrv->charge(1.234, 'myCurrency', $paymentType, 'myUrl', $customer, 'myId', $metadata, $basket, $card3ds);
        $this->assertSame($paymentType, $returnedCharge->getPayment()->getPaymentType());
    }

    /**
     * Verify chargeAuthorization calls chargePayment with the given payment object.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     * @throws ReflectionException
     */
    public function chargeAuthorizationShouldCallChargePaymentWithTheGivenPaymentObject()
    {
        $paymentObject  = (new Payment())->setId('myPaymentId');
        /** @var PaymentService|MockObject $paymentSrvMock */
        $paymentSrvMock = $this->getMockBuilder(PaymentService::class)->setMethods(['chargePayment'])->disableOriginalConstructor()->getMock();
        $paymentSrvMock->expects($this->exactly(2))->method('chargePayment')->withConsecutive([$paymentObject, null], [$paymentObject, 1.234]);

        $paymentSrvMock->setHeidelpay((new Heidelpay('s-priv-123'))->setPaymentService($paymentSrvMock));
        $paymentSrvMock->chargeAuthorization($paymentObject);
        $paymentSrvMock->chargeAuthorization($paymentObject, 1.234);
    }

    /**
     * Verify chargeAuthorization calls fetchPayment if the payment object is passed as id string.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     * @throws ReflectionException
     */
    public function chargeAuthorizationShouldCallFetchPaymentIfThePaymentIsPassedAsIdString()
    {
        /** @var ResourceServiceInterface|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchPayment'])->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->once())->method('fetchPayment')->willReturn(new Payment());
        /** @var PaymentService|MockObject $paymentSrvMock */
        $paymentSrvMock = $this->getMockBuilder(PaymentService::class)->setMethods(['chargePayment', 'getResourceService'])->disableOriginalConstructor()->getMock();
        $paymentSrvMock->expects($this->once())->method('chargePayment')->withAnyParameters();
        $paymentSrvMock->expects(self::once())->method('getResourceService')->willReturn($resourceSrvMock);

        $paymentSrvMock->chargeAuthorization('myPaymentId');
    }

    /**
     * Verify chargePayment will create a charge object and call create on ResourceService with it.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function chargePaymentShouldCallCreateOnResourceServiceWithNewCharge()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $payment   = (new Payment())->setParentResource($heidelpay)->setId('myPaymentId');

        /** @var ResourceService|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()->setMethods(['createResource'])->getMock();
        $resourceSrvMock->expects($this->once())->method('createResource')
            ->with($this->callback(static function ($charge) use ($payment) {
                /** @var Charge $charge */
                $newPayment = $charge->getPayment();
                return $charge instanceof Charge &&
                    $charge->getAmount() === 1.234 &&
                    $charge->getCurrency() === 'myTestCurrency' &&
                    $charge->getOrderId() === null &&
                    $charge->getInvoiceId() === null &&
                    $newPayment instanceof Payment &&
                    $newPayment === $payment &&
                    in_array($charge, $newPayment->getCharges(), true);
            }));

        $paymentSrv     = $heidelpay->setResourceService($resourceSrvMock)->getPaymentService();
        $returnedCharge = $paymentSrv->chargePayment($payment, 1.234, 'myTestCurrency');
        $this->assertArraySubset([$returnedCharge], $payment->getCharges());
    }

    /**
     * Verify chargePayment will set Ids if they are defined.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function chargePaymentShouldSetArgumentsInNewChargeObject()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $payment   = (new Payment())->setParentResource($heidelpay)->setId('myPaymentId');

        /** @var ResourceService|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()->setMethods(['createResource'])->getMock();
        $resourceSrvMock->expects($this->once())->method('createResource')
            ->with($this->callback(static function ($charge) use ($payment) {
                /** @var Charge $charge */
                $newPayment = $charge->getPayment();
                return $charge instanceof Charge &&
                    $charge->getAmount() === 1.234 &&
                    $charge->getCurrency() === 'myTestCurrency' &&
                    $charge->getOrderId() === 'orderId' &&
                    $charge->getInvoiceId() === 'invoiceId' &&
                    $newPayment instanceof Payment &&
                    $newPayment === $payment &&
                    in_array($charge, $newPayment->getCharges(), true);
            }));

        $paymentSrv     = $heidelpay->setResourceService($resourceSrvMock)->getPaymentService();
        $returnedCharge = $paymentSrv->chargePayment($payment, 1.234, 'myTestCurrency', 'orderId', 'invoiceId');
        $this->assertArraySubset([$returnedCharge], $payment->getCharges());
    }

    //</editor-fold>

    //<editor-fold desc="Cancel">

    /**
     * Verify cancelAuthorization will create a cancellation object and call create on ResourceService with it.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function cancelAuthorizationShouldCallCreateOnResourceServiceWithNewCancellation()
    {
        $heidelpay     = new Heidelpay('s-priv-123');
        $payment       = (new Payment())->setParentResource($heidelpay)->setId('myPaymentId');
        $authorization = (new Authorization())->setPayment($payment)->setId('s-aut-1');

        /** @var ResourceService|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()->setMethods(['createResource'])->getMock();
        $resourceSrvMock->expects($this->once())->method('createResource')
            ->with($this->callback(static function ($cancellation) use ($payment) {
                /** @var Cancellation $cancellation */
                $newPayment = $cancellation->getPayment();
                return $cancellation instanceof Cancellation &&
                    $cancellation->getAmount() === 12.122 &&
                    $newPayment instanceof Payment &&
                    $newPayment === $payment;
            }))->will($this->returnArgument(0));

        $cancelSrv            = $heidelpay->setResourceService($resourceSrvMock)->getCancelService();
        $returnedCancellation = $cancelSrv->cancelAuthorization($authorization, 12.122);

        $this->assertSame(12.122, $returnedCancellation->getAmount());
        $this->assertSame($payment, $returnedCancellation->getPayment());
    }

    /**
     * Verify cancelAuthorization will create a cancellation object and call create on ResourceService with it.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function cancelAuthorizationShouldNotAddCancellationIfCancellationFails(): void
    {
        $heidelpay     = new Heidelpay('s-priv-123');
        $payment       = (new Payment())->setParentResource($heidelpay)->setId('myPaymentId');
        $authorization = (new Authorization())->setPayment($payment)->setId('s-aut-1');

        /** @var ResourceService|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()->setMethods(['createResource'])->getMock();
        $cancellationException       = new HeidelpayApiException(
            'Cancellation failed',
            'something went wrong',
            ApiResponseCodes::API_ERROR_ALREADY_CANCELLED
        );
        $resourceSrvMock->expects($this->once())->method('createResource')->willThrowException($cancellationException);

        $cancelSrv = $heidelpay->setResourceService($resourceSrvMock)->getCancelService();
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_ALREADY_CANCELLED);
        $cancelSrv->cancelAuthorization($authorization, 12.122);
        $this->assertCount(0, $authorization->getCancellations());
    }

    /**
     * Verify cancelAuthorizationByPayment will propagate to cancelAuthorization method.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function cancelAuthorizationByPaymentShouldCallCancelAuthorization()
    {
        $authorization = (new Authorization())->setId('s-aut-1');
        $heidelpay = new Heidelpay('s-priv-1234');

        /** @var ResourceService|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchAuthorization'])->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->exactly(2))->method('fetchAuthorization')->willReturn($authorization);
        /** @var CancelService|MockObject $cancelSrvMock */
        $cancelSrvMock = $this->getMockBuilder(CancelService::class)->setMethods(['cancelAuthorization'])->disableOriginalConstructor()->getMock();
        $cancelSrvMock->expects($this->exactly(2))->method('cancelAuthorization')->withConsecutive([$authorization, null], [$authorization, 1.123]);
        $heidelpay->setResourceService($resourceSrvMock)->setCancelService($cancelSrvMock);

        $cancelSrvMock->cancelAuthorizationByPayment(new Payment());
        $cancelSrvMock->cancelAuthorizationByPayment(new Payment(), 1.123);
    }

    /**
     * Verify cancelChargeById fetches Charge and propagates to cancelCharge method.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function cancelChargeByIdShouldFetchChargeAndPropagateToCancelCharge()
    {
        $payment = (new Payment())->setId('myPaymentId');
        $charge  = new Charge();

        /** @var ResourceServiceInterface|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchChargeById'])->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->exactly(2))->method('fetchChargeById')->with($payment, 's-chg-1')->willReturn($charge);

        /** @var CancelServiceInterface|MockObject $cancelSrvMock */
        $cancelSrvMock = $this->getMockBuilder(CancelService::class)->setMethods(['cancelCharge', 'getResourceService'])->disableOriginalConstructor()->getMock();
        $cancelSrvMock->expects($this->exactly(2))->method('cancelCharge')->withConsecutive([$charge], [$charge, 10.11]);
        $cancelSrvMock->expects($this->exactly(2))->method('getResourceService')->willReturn($resourceSrvMock);

        $cancelSrvMock->cancelChargeById($payment, 's-chg-1');
        $cancelSrvMock->cancelChargeById($payment, 's-chg-1', 10.11);
    }

    /**
     * Verify cancelCharge creates new Cancellation and calls create on resourceService with it.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws ReflectionException
     * @throws HeidelpayApiException
     */
    public function cancelChargeShouldCreateCancellationAndCallsCreate()
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        $cancelSrv = $heidelpay->getCancelService();
        $payment   = (new Payment())->setParentResource($heidelpay);
        $charge    = (new Charge())->setPayment($payment);

        /** @var ResourceService|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['createResource'])->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->once())->method('createResource')
            ->with($this->callback(static function ($cancellation) use ($payment, $charge) {
                return $cancellation instanceof Cancellation &&
                    $cancellation->getAmount() === 12.22 &&
                    $cancellation->getPayment() === $payment &&
                    $cancellation->getParentResource() === $charge;
            }));
        $heidelpay->setResourceService($resourceSrvMock);

        $cancelSrv->cancelCharge($charge, 12.22);
    }

    //</editor-fold>

    //<editor-fold desc="Shipment">

    /**
     * Verify ship method will create a new Shipment, add it to the given payment object and call create on
     * ResourceService with the shipment object.
     *
     * @test
     *
     * @throws ReflectionException
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function shipShouldCreateShipmentAndCallCreateOnResourceServiceWithIt()
    {
        $heidelpay  = new Heidelpay('s-priv-1234');
        $payment    = new Payment();

        /** @var ResourceService|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['createResource'])->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->exactly(2))->method('createResource')
            ->with($this->callback(static function ($shipment) use ($payment) {
                return $shipment instanceof Shipment &&
                    $shipment->getPayment() === $payment &&
                    $shipment->getParentResource() === $payment;
            }));
        $heidelpay->setResourceService($resourceSrvMock);

        $heidelpay->getPaymentService()->ship($payment);
        $this->assertCount(1, $payment->getShipments());

        $heidelpay->getPaymentService()->ship($payment);
        $this->assertCount(2, $payment->getShipments());
    }

    //</editor-fold>

    //<editor-fold desc="Payout">

    /**
     * Verify payout method calls payout with payment.
     *
     * @test
     *
     * @throws ReflectionException
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function payoutShouldCreatePaymentAndCallPayoutWithPayment()
    {
        $paymentType = (new SepaDirectDebit('1234'))->setId('typeId');
        $customer    = (new Customer())->setId('customerId');
        $metadata    = (new Metadata())->setId('metadataId');

        /** @var ResourceService|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()->setMethods(['createResource'])->getMock();
        $resourceSrvMock->expects(self::once())->method('createResource')
            ->with(self::callback(static function ($payout) use ($customer, $metadata) {
                return $payout instanceof Payout &&
                    $payout->getAmount() === 1.23 &&
                    $payout->getCurrency() === 'testCurrency' &&
                    $payout->getPayment() instanceof Payment &&
                    $payout->getReturnUrl() === 'http://return.url' &&
                    $customer === $payout->getPayment()->getCustomer() &&
                    $metadata === $payout->getPayment()->getMetadata();
            }));
        $heidelpay = (new Heidelpay('s-priv-123'))->setResourceService($resourceSrvMock);
        $heidelpay->getPaymentService()->payout(1.23, 'testCurrency', $paymentType, 'http://return.url', $customer, 'OrderId', $metadata);
    }

    /**
     * Verify payoutWithPayment calls create for a new payout using the passed values.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function payoutShouldCreateNewPayout()
    {
        // we provide some fake resources with ids to avoid them to be automatically created
        $customer  = (new Customer())->setId('id-1');
        $basket    = (new Basket())->setId('id-2');
        $metadata  = (new Metadata())->setId('id-3');
        $heidelpay = new Heidelpay('s-priv-123');

        /** @var ResourceService|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()->setMethods(['createResource'])->getMock();
        $resourceSrvMock->expects($this->once())->method('createResource')
            ->with($this->callback(static function ($payout) use ($customer, $basket, $metadata) {
                /** @var Payout $payout */
                $newPayment = $payout->getPayment();
                return $payout instanceof Payout &&
                    $payout->getAmount() === 1.234 &&
                    $payout->getCurrency() === 'EUR' &&
                    $payout->getOrderId() === 'id-4' &&
                    $payout->getReturnUrl() === 'url' &&
                    $newPayment instanceof Payment &&
                    $newPayment->getCustomer() === $customer &&
                    $newPayment->getMetadata() === $metadata &
                    $newPayment->getBasket() === $basket &&
                    $newPayment->getPayout() === $payout;
            }));
        $paymentSrv     = $heidelpay->setResourceService($resourceSrvMock)->getPaymentService();
        $paymentType    = (new PayPal())->setId('id');
        $returnedPayout = $paymentSrv->payout(1.234, 'EUR', $paymentType, 'url', $customer, 'id-4', $metadata, $basket);
        $this->assertEquals(
            [
                'amount' => 1.234,
                'currency' => 'EUR',
                'orderId' => 'id-4',
                'returnUrl' => 'url',
                'resources' => ['basketId' => 'id-2', 'customerId' => 'id-1', 'metadataId' => 'id-3', 'typeId' => 'id']
            ], $returnedPayout->expose());
    }

    //</editor-fold>

    //<editor-fold desc="PayPage">

    /** @noinspection PhpDocRedundantThrowsInspection */

    /**
     * Verify initPayPage creates a payment with resources and calls create with said payment.
     *
     * @test
     *
     * @dataProvider paymentShouldBeCreatedByInitPayPageDP
     *
     * @param string $action
     *
     * @throws HeidelpayApiException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws Exception
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     */
    public function paymentShouldBeCreatedByInitPayPage(string $action)
    {
        $method = 'initPayPage' . $action;

        /** @var ResourceService|MockObject $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['createResource'])->disableOriginalConstructor()->getMock();
        $paymentSrv = (new Heidelpay('s-priv-1234'))->setResourceService($resourceSrvMock)->getPaymentService();

        // when
        $paypage  = new Paypage(123.4, 'CHF', 'url');
        $basket   = (new Basket())->setId('basketId');
        $customer = (new Customer())->setId('customerId');
        $metadata = (new Metadata())->setId('metadataId');

        // should
        $resourceSrvMock->expects($this->once())->method('createResource')
            ->with($this->callback(static function ($paypage) use ($basket, $customer, $metadata, $action) {
                return $paypage instanceof Paypage &&
                    $paypage->getPayment() instanceof Payment &&
                    $basket === $paypage->getBasket() &&
                    $customer === $paypage->getCustomer() &&
                    $metadata === $paypage->getMetadata() &&
                    $action === $paypage->getAction();
            }));

        // when
        $paymentSrv->$method($paypage, $customer, $basket, $metadata);
    }

    //</editor-fold>

    //<editor-fold desc="Hire Purchase">

    /**
     * Verify fetch hdd instalment plans.
     *
     * @test
     *
     * @throws Exception
     * @throws ReflectionException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     * @throws HeidelpayApiException                          A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException                               A RuntimeException is thrown when there is an error while using the SDK.
     * @throws \Exception
     */
    public function fetchInstalmentPlansWillCallFetchOnResourceService()
    {
        $heidelpay = new Heidelpay('s-priv-1234');
        /** @var MockObject|ResourceService $resourceSrvMock */
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setConstructorArgs(['heidelpay' => $heidelpay])->setMethods(['fetchResource'])->getMock();
        $heidelpay->setResourceService($resourceSrvMock);

        $date = $this->getYesterdaysTimestamp();
        $resourceSrvMock->expects($this->once())->method('fetchResource')
            ->with($this->callback(static function ($param) use ($date) {
                return $param instanceof InstalmentPlans &&
                    $param->getAmount() === 12.23 &&
                    $param->getCurrency() === 'EUR' &&
                    $param->getEffectiveInterest() === 4.99 &&
                    $param->getOrderDate() === $date->format('Y-m-d') &&
                    $param->getParentResource() instanceof HirePurchaseDirectDebit;
            }))->willReturn(new InstalmentPlans(12.23, 'EUR', 4.99, $date));
        $heidelpay->getPaymentService()->fetchDirectDebitInstalmentPlans(12.23, 'EUR', 4.99, $date);
    }

    //</editor-fold>

    //<editor-fold desc="DataProviders">

    /**
     * @return array
     */
    public function card3dsDataProvider(): array
    {
        return [
            'default' => [null],
            'non 3ds' => [false],
            '3ds'     => [true]
        ];
    }

    /**
     * @return array
     */
    public function paymentShouldBeCreatedByInitPayPageDP(): array
    {
        return [
            TransactionTypes::CHARGE        => [TransactionTypes::CHARGE],
            TransactionTypes::AUTHORIZATION => [TransactionTypes::AUTHORIZATION]
        ];
    }

    //</editor-fold>
}
