<?php
/**
 * This class defines unit tests to verify functionality of the resource name service.
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

use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Heidelpay;
use heidelpay\MgwPhpSdk\Resources\Customer;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Sofort;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Cancellation;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;
use heidelpay\MgwPhpSdk\Services\PaymentService;
use heidelpay\MgwPhpSdk\Services\ResourceService;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;

class PaymentServiceTest extends TestCase
{
    /**
     * Verify setters and getters work properly.
     *
     * @test
     *
     * @throws \RuntimeException
     */
    public function gettersAndSettersShouldWorkProperly()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $paymentService = new PaymentService($heidelpay);
        $this->assertSame($heidelpay, $paymentService->getHeidelpay());
        $this->assertSame($heidelpay->getResourceService(), $paymentService->getResourceService());

        $heidelpay2 = new Heidelpay('s-priv-1234');
        $resourceService2 = new ResourceService($heidelpay2);
        $paymentService->setResourceService($resourceService2);
        $this->assertSame($heidelpay, $paymentService->getHeidelpay());
        $this->assertNotSame($heidelpay2->getResourceService(), $paymentService->getResourceService());
        $this->assertSame($resourceService2, $paymentService->getResourceService());

        $paymentService->setHeidelpay($heidelpay2);
        $this->assertSame($heidelpay2, $paymentService->getHeidelpay());
        $this->assertNotSame($heidelpay2->getResourceService(), $paymentService->getResourceService());
    }

    /**
     * Verify authorize method calls authorize with payment.
     *
     * @test
     *
     * @throws Exception
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function authorizeShouldCreatePaymentAndCallAuthorizeWithPayment()
    {
        $paymentType = (new Sofort())->setId('typeId');
        $customer = (new Customer())->setId('customerId');

        $paymentSrvMock = $this->getMockBuilder(PaymentService::class)->disableOriginalConstructor()
            ->setMethods(['authorizeWithPayment'])->getMock();
        $paymentSrvMock->expects($this->exactly(3))->method('authorizeWithPayment')
            ->withConsecutive(
                [1.23, 'testCurrency', $this->isInstanceOf(Payment::class), 'http://return.url'],
                [1.23, 'testCurrency', $this->isInstanceOf(Payment::class), 'http://return.url', $customer],
                [1.23, 'testCurrency', $this->isInstanceOf(Payment::class), 'http://return.url', $customer, 'OrderId']
            );

        /** @var PaymentService $paymentSrvMock */
        $paymentSrvMock->setHeidelpay(new Heidelpay('s-priv-123'));
        $paymentSrvMock->authorize(1.23, 'testCurrency', $paymentType, 'http://return.url');
        $paymentSrvMock->authorize(1.23, 'testCurrency', $paymentType, 'http://return.url', $customer);
        $paymentSrvMock->authorize(1.23, 'testCurrency', $paymentType, 'http://return.url', $customer, 'OrderId');
    }

    /**
     * Verify authorizeWithPayment calls create for a new authorization using the passed values.
     *
     * @test
     *
     * @throws Exception
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     */
    public function authorizeWithPaymentShouldCallCreateOnResourceServiceWithANewAuthorization()
    {
        $customer = (new Customer())->setId('myCustomerId');
        $heidelpay = new Heidelpay('s-priv-123');
        $payment = (new Payment())->setParentResource($heidelpay)->setId('myPaymentId');

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()
            ->setMethods(['create'])->getMock();
        $resourceSrvMock->expects($this->once())->method('create')->with(
            $this->callback(
                function ($authorize) use ($customer, $payment) {
                    /** @var Authorization $authorize */
                    $newPayment = $authorize->getPayment();
                    return $authorize instanceof Authorization &&
                           $authorize->getAmount() === 1.234 &&
                           $authorize->getCurrency() === 'myTestCurrency' &&
                           $authorize->getOrderId() === 'myOrderId' &&
                           $authorize->getReturnUrl() === 'myTestUrl' &&
                           $newPayment instanceof Payment &&
                           $newPayment === $payment &&
                           $newPayment->getCustomer() === $customer &&
                           $newPayment->getAuthorization() === $authorize;
                }
            )
        );

        /** @var ResourceService $resourceSrvMock */
        $paymentSrv = (new PaymentService($heidelpay))->setResourceService($resourceSrvMock);
        $returnedAuth =
            $paymentSrv->authorizeWithPayment(1.234, 'myTestCurrency', $payment, 'myTestUrl', $customer, 'myOrderId');
        $this->assertSame($payment->getAuthorization(), $returnedAuth);
    }

    /**
     * Verify charge method calls create with a charge object on resource service.
     *
     * @test
     *
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws ExpectationFailedException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function chargeShouldCreateAPaymentAndCallCreateOnResourceServiceWithPayment()
    {
        $customer = (new Customer())->setId('myCustomerId');
        $heidelpay = new Heidelpay('s-priv-123');
        $paymentType = (new Sofort())->setId('myPaymentTypeId');

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()
            ->setMethods(['create'])->getMock();
        $resourceSrvMock->expects($this->once())->method('create')->with(
            $this->callback(
                function ($charge) use ($customer, $paymentType) {
                    /** @var Charge $charge */
                    $newPayment = $charge->getPayment();
                    return $charge instanceof Charge &&
                        $charge->getAmount() === 1.234 &&
                        $charge->getCurrency() === 'myTestCurrency' &&
                        $charge->getOrderId() === 'myOrderId' &&
                        $charge->getReturnUrl() === 'myTestUrl' &&
                        $newPayment instanceof Payment &&
                        $newPayment->getCustomer() === $customer &&
                        $newPayment->getPaymentType() === $paymentType &&
                        \in_array($charge, $newPayment->getCharges(), true);
                }
            )
        );

        /** @var ResourceService $resourceSrvMock */
        $paymentSrv = (new PaymentService($heidelpay))->setResourceService($resourceSrvMock);
        $returnedCharge =
            $paymentSrv->charge(1.234, 'myTestCurrency', $paymentType, 'myTestUrl', $customer, 'myOrderId');
        $this->assertSame($paymentType, $returnedCharge->getPayment()->getPaymentType());
    }

    /**
     * Verify chargeAuthorization calls chargePayment with the given payment object.
     *
     * @test
     *
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     * @throws \ReflectionException
     */
    public function chargeAuthorizationShouldCallChargePaymentWithTheGivenPaymentObject()
    {
        $paymentObject = (new Payment())->setId('myPaymentId');
        $paymentSrv = $this->getMockBuilder(PaymentService::class)->setMethods(['chargePayment'])
            ->disableOriginalConstructor()->getMock();
        $paymentSrv->expects($this->exactly(2))->method('chargePayment')
            ->withConsecutive([$paymentObject, null], [$paymentObject, 1.234]);

        /** @var PaymentService $paymentSrv */
        $paymentSrv->chargeAuthorization($paymentObject);
        $paymentSrv->chargeAuthorization($paymentObject, 1.234);
    }

    /**
     * Verify chargeAuthorization calls fetchPayment if the payment object is passed as id string.
     *
     * @test
     *
     * @throws \RuntimeException
     * @throws HeidelpayApiException
     * @throws \ReflectionException
     */
    public function chargeAuthorizationShouldCallFetchPaymentIfThePaymentIsPassedAsIdString()
    {
        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchPayment'])
            ->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->once())->method('fetchPayment')->willReturn(new Payment());

        $paymentSrvMock = $this->getMockBuilder(PaymentService::class)->setMethods(['chargePayment'])
            ->disableOriginalConstructor()->getMock();
        $paymentSrvMock->expects($this->once())->method('chargePayment')->withAnyParameters();

        /**
         * @var PaymentService  $paymentSrvMock
         * @var ResourceService $resourceSrvMock
         */
        $paymentSrvMock->setResourceService($resourceSrvMock);
        $paymentSrvMock->chargeAuthorization('myPaymentId');
    }

    /**
     * Verify chargePayment will create a charge object and call create on ResourceService with it.
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
    public function chargePaymentShouldCallCreateOnResourceServiceWithNewCharge()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $payment = (new Payment())->setParentResource($heidelpay)->setId('myPaymentId');

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()
            ->setMethods(['create'])->getMock();
        $resourceSrvMock->expects($this->once())->method('create')->with(
            $this->callback(
                function ($charge) use ($payment) {
                    /** @var Charge $charge */
                    $newPayment = $charge->getPayment();
                    return $charge instanceof Charge &&
                        $charge->getAmount() === 1.234 &&
                        $charge->getCurrency() === 'myTestCurrency' &&
                        $newPayment instanceof Payment &&
                        $newPayment === $payment &&
                        \in_array($charge, $newPayment->getCharges(), true);
                }
            )
        );

        /** @var ResourceService $resourceSrvMock */
        $paymentSrv = (new PaymentService($heidelpay))->setResourceService($resourceSrvMock);
        $returnedCharge = $paymentSrv->chargePayment($payment, 1.234, 'myTestCurrency');
        $this->assertArraySubset([$returnedCharge], $payment->getCharges());
    }

    /**
     * Verify cancelAuthorization will create a cancellation object and call create on ResourceService with it.
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
    public function cancelAuthorizationShouldCallCreateOnResourceServiceWithNewCancellation()
    {
        $heidelpay = new Heidelpay('s-priv-123');
        $payment = (new Payment())->setParentResource($heidelpay)->setId('myPaymentId');
        $authorization = (new Authorization())->setPayment($payment)->setId('s-aut-1');

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()
            ->setMethods(['create'])->getMock();
        $resourceSrvMock->expects($this->once())->method('create')->with(
            $this->callback(
                function ($cancellation) use ($authorization, $payment) {
                    /** @var Cancellation $cancellation */
                    $newPayment = $cancellation->getPayment();
                    return $cancellation instanceof Cancellation &&
                        $cancellation->getAmount() === 12.122 &&
                        $newPayment instanceof Payment &&
                        $newPayment === $payment &&
                        \in_array($cancellation, $authorization->getCancellations(), true);
                }
            )
        );

        /** @var ResourceService $resourceSrvMock */
        $paymentSrv = (new PaymentService($heidelpay))->setResourceService($resourceSrvMock);
        $returnedCancellation = $paymentSrv->cancelAuthorization($authorization, 12.122);
        $this->assertArraySubset([$returnedCancellation], $authorization->getCancellations());
    }

    /**
     * Verify cancelAuthorizationByPayment will propagate to cancelAuthorization method.
     *
     * @test
     *
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function cancelAuthorizationByPaymentShouldCallCancelAuthorization()
    {
        $authorization = (new Authorization())->setId('s-aut-1');

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchAuthorization'])
            ->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->exactly(2))->method('fetchAuthorization')->willReturn($authorization);

        $paymentSrvMock = $this->getMockBuilder(PaymentService::class)->setMethods(['cancelAuthorization'])
            ->disableOriginalConstructor()->getMock();
        $paymentSrvMock->expects($this->exactly(2))->method('cancelAuthorization')->withConsecutive(
            [$authorization, null],
            [$authorization, 1.123]
        );

        /**
         * @var PaymentService  $paymentSrvMock
         * @var ResourceService $resourceSrvMock
         */
        $paymentSrvMock->setResourceService($resourceSrvMock);

        /** @var PaymentService $paymentSrvMock */
        $paymentSrvMock->cancelAuthorizationByPayment(new Payment());
        $paymentSrvMock->cancelAuthorizationByPayment(new Payment(), 1.123);
    }

    /**
     * Verify cancelChargeById fetches Charge and propagates to cancelCharge method.
     *
     * @test
     *
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws \ReflectionException
     * @throws \RuntimeException
     */
    public function cancelChargeByIdShouldFetchChargeAndPropagateToCancelCharge()
    {
        $payment = (new Payment())->setId('myPaymentId');
        $charge = new Charge();

        $resourceSrvMock = $this->getMockBuilder(ResourceService::class)->setMethods(['fetchChargeById'])
            ->disableOriginalConstructor()->getMock();
        $resourceSrvMock->expects($this->exactly(2))->method('fetchChargeById')->with($payment, 's-chg-1')
            ->willReturn($charge);

        $paymentSrvMock = $this->getMockBuilder(PaymentService::class)->setMethods(['cancelCharge'])
            ->disableOriginalConstructor()->getMock();
        $paymentSrvMock->expects($this->exactly(2))->method('cancelCharge')->withConsecutive(
            [$charge],
            [$charge, 10.11]
        );

        /**
         * @var PaymentService  $paymentSrvMock
         * @var ResourceService $resourceSrvMock
         */
        $paymentSrvMock->setResourceService($resourceSrvMock);

        $paymentSrvMock->cancelChargeById($payment, 's-chg-1');
        $paymentSrvMock->cancelChargeById($payment, 's-chg-1', 10.11);
    }
}
