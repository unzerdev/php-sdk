<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify cancel functionality of the CancelService.
 *
 * Copyright (C) 2019 heidelpay GmbH
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
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\EmbeddedResources\Amount;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\Services\CancelService;
use heidelpayPHP\Services\ResourceService;
use heidelpayPHP\test\BasePaymentTest;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

class CancelServiceTest extends BasePaymentTest
{
    //<editor-fold desc="Deprecated since 1.2.3.0">

    /**
     * Verify payment:cancel calls cancelAllCharges and cancelAuthorizationAmount and returns first charge cancellation
     * object.
     *
     * @test
     *
     * @deprecated since 1.2.3.0
     */
    public function cancelShouldCallCancelAllChargesAndCancelAuthorizationAndReturnFirstChargeCancellationObject(): void
    {
        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['cancelAmount'])->getMock();
        $cancellation = new Cancellation(1.0);
        $paymentMock->expects($this->once())->method('cancelAmount')->willReturn([$cancellation]);

        /** @var Payment $paymentMock */
        $this->assertSame($cancellation, $paymentMock->cancel());
    }

    /**
     * Verify payment:cancel throws Exception if no cancellation and no auth existed to be cancelled.
     *
     * @test
     *
     * @deprecated since 1.2.3.0
     */
    public function cancelShouldThrowExceptionIfNoTransactionExistsToBeCancelled(): void
    {
        /** @var CancelService|MockObject $cancelSrvMock */
        $cancelSrvMock = $this->getMockBuilder(CancelService::class)->disableOriginalConstructor()->setMethods(['cancelAllCharges', 'cancelAuthorization'])->getMock();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This Payment could not be cancelled.');

        $heidelpay = new Heidelpay('s-priv-1234');
        $heidelpay->setCancelService($cancelSrvMock);
        $payment = (new Payment())->setParentResource($heidelpay);

        $payment->cancel();
    }

    /**
     * Verify cancel all charges will call cancel on each existing charge of the payment and will return a list of
     * cancels and exceptions.
     *
     * @test
     *
     * @deprecated since 1.2.3.0
     */
    public function cancelAllChargesShouldCallCancelOnAllChargesAndReturnCancelsAndExceptions(): void
    {
        $cancellation1 = new Cancellation(1.0);
        $cancellation2 = new Cancellation(2.0);
        $cancellation3 = new Cancellation(3.0);
        $exception1 = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_ALREADY_CHARGED_BACK);
        $exception2 = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_ALREADY_CHARGED_BACK);

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
        $payment->addCharge($chargeMock1)->addCharge($chargeMock2)->addCharge($chargeMock3)->addCharge($chargeMock4)->addCharge($chargeMock5);

        [$cancellations, $exceptions] = $payment->cancelAllCharges();
        $this->assertEquals([$cancellation1, $cancellation2, $cancellation3], $cancellations);
        $this->assertEquals([$exception1, $exception2], $exceptions);
    }

    /**
     * Verify cancelAllCharges will throw any exception with Code different to
     * ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CANCELED.
     *
     * @test
     *
     * @deprecated since 1.2.3.0
     */
    public function cancelAllChargesShouldThrowChargeCancelExceptionsOtherThanAlreadyCharged(): void
    {
        $ex1 = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_ALREADY_CHARGED_BACK);
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

    //</editor-fold>

    /**
     * Verify cancelAmount will call cancelAuthorizationAmount with the amountToCancel.
     * When cancelAmount is <= the value of the cancellation it Will return auth cancellation only.
     * Charge cancel will not be called if the amount to cancel has been cancelled on the authorization.
     *
     * @test
     */
    public function cancelAmountShouldCallCancelAuthorizationAmount(): void
    {
        /** @var MockObject|CancelService $cancelSrvMock */
        $cancelSrvMock = $this->getMockBuilder(CancelService::class)->disableOriginalConstructor()->setMethods(['cancelPaymentAuthorization'])->getMock();
        $this->heidelpay->setCancelService($cancelSrvMock);

        /** @var MockObject|Charge $chargeMock */
        $chargeMock = $this->getMockBuilder(Charge::class)->setMethods(['cancel'])->getMock();

        $payment = (new Payment($this->heidelpay))->setAuthorization(new Authorization(12.3));

        $cancellation = new Cancellation(12.3);
        $cancelSrvMock->expects($this->exactly(2))->method('cancelPaymentAuthorization')->willReturn($cancellation);
        $chargeMock->expects($this->never())->method('cancel');

        $this->assertEquals([$cancellation], $payment->cancelAmount(10.0));
        $this->assertEquals([$cancellation], $payment->cancelAmount(12.3));
    }

    /**
     * Verify that cancel amount will be cancelled on charges if auth does not exist.
     *
     * @test
     */
    public function chargesShouldBeCancelledIfAuthDoesNotExist1(): void
    {
        /** @var MockObject|CancelService $cancelSrvMock */
        $cancelSrvMock = $this->getMockBuilder(CancelService::class)->disableOriginalConstructor()->setMethods(['cancelPaymentAuthorization'])->getMock();
        $this->heidelpay->setCancelService($cancelSrvMock);

        /** @var MockObject|Charge $chargeMock */
        $chargeMock = $this->getMockBuilder(Charge::class)->setMethods(['cancel'])->setConstructorArgs([10.0])->getMock();

        $cancellation = new Cancellation(10.0);

        $cancelSrvMock->expects($this->once())->method('cancelPaymentAuthorization')->willReturn(null);
        /** @noinspection PhpParamsInspection */
        $chargeMock->expects($this->once())->method('cancel')->with(10.0, 'CANCEL')->willReturn($cancellation);

        $payment = (new Payment($this->heidelpay))->addCharge($chargeMock);

        $this->assertEquals([$cancellation], $payment->cancelAmount(10.0));
    }

    /**
     * Verify that cancel amount will be cancelled on charges if auth does not exist.
     *
     * @test
     */
    public function chargesShouldBeCancelledIfAuthDoesNotExist2(): void
    {
        /** @var MockObject|CancelService $cancelSrvMock */
        $cancelSrvMock = $this->getMockBuilder(CancelService::class)->disableOriginalConstructor()->setMethods(['cancelPaymentAuthorization'])->getMock();
        $this->heidelpay->setCancelService($cancelSrvMock);
        /** @var MockObject|Charge $charge1Mock */
        $charge1Mock = $this->getMockBuilder(Charge::class)->setMethods(['cancel'])->setConstructorArgs([10.0])->getMock();
        /** @var MockObject|Charge $charge2Mock */
        $charge2Mock = $this->getMockBuilder(Charge::class)->setMethods(['cancel'])->setConstructorArgs([12.3])->getMock();

        $cancellation1 = new Cancellation(10.0);
        $cancellation2 = new Cancellation(2.3);

        $cancelSrvMock->expects($this->exactly(3))->method('cancelPaymentAuthorization')->willReturn(null);
        $charge1Mock->expects($this->exactly(3))->method('cancel')->withConsecutive([10.0, 'CANCEL'], [null, 'CANCEL'], [null, 'CANCEL'])->willReturn($cancellation1);
        $charge2Mock->expects($this->exactly(2))->method('cancel')->withConsecutive([2.3, 'CANCEL'], [null, 'CANCEL'])->willReturn($cancellation2);

        $payment = (new Payment($this->heidelpay))->setAuthorization(new Authorization(12.3));
        $payment->addCharge($charge1Mock)->addCharge($charge2Mock);

        $this->assertEquals([$cancellation1], $payment->cancelAmount(10.0));
        $this->assertEquals([$cancellation1, $cancellation2], $payment->cancelAmount(12.3));
        $this->assertEquals([$cancellation1, $cancellation2], $payment->cancelAmount());
    }

    /**
     * Verify certain errors are allowed during cancellation and will be ignored.
     *
     * @test
     * @dataProvider allowedErrorCodesDuringChargeCancel
     *
     * @param string $allowedExceptionCode
     * @param bool   $shouldHaveThrownException
     */
    public function verifyAllowedErrorsWillBeIgnoredDuringChargeCancel($allowedExceptionCode, $shouldHaveThrownException): void
    {
        /** @var MockObject|CancelService $cancelSrvMock */
        $cancelSrvMock = $this->getMockBuilder(CancelService::class)->disableOriginalConstructor()->setMethods(['cancelPaymentAuthorization'])->getMock();
        $this->heidelpay->setCancelService($cancelSrvMock);
        /** @var MockObject|Charge $chargeMock */
        $chargeMock = $this->getMockBuilder(Charge::class)->setMethods(['cancel'])->disableOriginalConstructor()->getMock();

        $allowedException = new HeidelpayApiException(null, null, $allowedExceptionCode);
        $chargeMock->method('cancel')->willThrowException($allowedException);

        $payment = (new Payment($this->heidelpay))->addCharge($chargeMock);

        try {
            $this->assertEquals([], $payment->cancelAmount(12.3));
            $this->assertFalse($shouldHaveThrownException, 'Exception should have been thrown here!');
        } catch (HeidelpayApiException $e) {
            $this->assertTrue($shouldHaveThrownException, "Exception should not have been thrown here! ({$e->getCode()})");
        }
    }

    /**
     * Verify cancelAuthorizationAmount will call cancel on the authorization and will return a list of cancels.
     *
     * @test
     */
    public function cancelAuthorizationAmountShouldCallCancelOnTheAuthorization(): void
    {
        /** @var Authorization|MockObject $authorizationMock */
        $authorizationMock = $this->getMockBuilder(Authorization::class)->setMethods(['cancel'])->getMock();
        $cancellation = new Cancellation(1.0);
        $authorizationMock->expects($this->once())->method('cancel')->willReturn($cancellation);

        /** @var Payment|MockObject $paymentMock */
        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getAuthorization'])->getMock();
        $paymentMock->expects($this->once())->method('getAuthorization')->willReturn($authorizationMock);
        $paymentMock->setParentResource($this->heidelpay)->setAuthorization($authorizationMock);

        $this->assertEquals($cancellation, $paymentMock->cancelAuthorizationAmount());
    }

    /**
     * Verify cancelAuthorizationAmount will call cancel the given amount on the authorization of the payment.
     * Cancellation amount will be the remaining amount of the payment at max.
     *
     * @test
     */
    public function cancelAuthorizationAmountShouldCallCancelWithTheRemainingAmountAtMax(): void
    {
        $cancellation = new Cancellation();

        /** @var MockObject|Authorization $authorizationMock */
        $authorizationMock = $this->getMockBuilder(Authorization::class)->setConstructorArgs([100.0])->setMethods(['cancel'])->getMock();
        $authorizationMock->expects($this->exactly(4))->method('cancel')->withConsecutive([null], [50.0], [100.0], [100.0])->willReturn($cancellation);

        /** @var Payment|MockObject $paymentMock */
        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getAuthorization', 'getAmount'])->getMock();
        $amountObj   = new Amount();
        $amountObj->handleResponse((object)['remaining' => 100.0]);
        $paymentMock->method('getAmount')->willReturn($amountObj);
        $paymentMock->expects($this->exactly(4))->method('getAuthorization')->willReturn($authorizationMock);
        $paymentMock->setParentResource($this->heidelpay);

        $paymentMock->setAuthorization($authorizationMock);
        $this->assertEquals($cancellation, $paymentMock->cancelAuthorizationAmount());
        $this->assertEquals($cancellation, $paymentMock->cancelAuthorizationAmount(50.0));
        $this->assertEquals($cancellation, $paymentMock->cancelAuthorizationAmount(100.0));
        $this->assertEquals($cancellation, $paymentMock->cancelAuthorizationAmount(101.0));
    }

    /**
     * Verify certain errors are allowed during cancellation and will be ignored.
     *
     * @test
     * @dataProvider allowedErrorCodesDuringAuthCancel
     *
     * @param string $exceptionCode
     * @param bool   $shouldHaveThrownException
     */
    public function verifyAllowedErrorsWillBeIgnoredDuringAuthorizeCancel($exceptionCode, $shouldHaveThrownException): void
    {
        /** @var MockObject|Payment $paymentMock */
        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getAuthorization'])->getMock();

        /** @var MockObject|Authorization $authMock */
        $authMock = $this->getMockBuilder(Authorization::class)->setMethods(['cancel'])->disableOriginalConstructor()->getMock();

        $exception = new HeidelpayApiException(null, null, $exceptionCode);
        $authMock->method('cancel')->willThrowException($exception);
        $paymentMock->method('getAuthorization')->willReturn($authMock);
        $paymentMock->getAmount()->handleResponse((object)['remaining' => 100.0]);
        $paymentMock->setParentResource($this->heidelpay);

        try {
            $this->assertEquals(null, $paymentMock->cancelAuthorizationAmount(12.3));
            $this->assertFalse($shouldHaveThrownException, 'Exception should have been thrown here!');
        } catch (HeidelpayApiException $e) {
            $this->assertTrue($shouldHaveThrownException, "Exception should not have been thrown here! ({$e->getCode()})");
        }
    }

    /**
     * Verify cancelAuthorizationAmount will stop processing if there is no amount to cancel.
     *
     * @test
     */
    public function cancelAuthorizationAmountWillNotCallCancelIfThereIsNoOpenAmount(): void
    {
        /** @var MockObject|Payment $paymentMock */
        /** @var MockObject|Authorization $authMock */
        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getAuthorization'])->getMock();
        $authMock = $this->getMockBuilder(Authorization::class)->setMethods(['cancel'])->disableOriginalConstructor()->getMock();
        $paymentMock->method('getAuthorization')->willReturn($authMock);
        $authMock->expects(self::never())->method('cancel');
        $paymentMock->getAmount()->handleResponse((object)['remaining' => 0.0]);

        $paymentMock->setParentResource($this->heidelpay);

        $paymentMock->cancelAuthorizationAmount(12.3);
        $paymentMock->cancelAuthorizationAmount(0.0);
    }

    /**
     * Verify cancelPayment will fetch payment if the payment is referenced by paymentId.
     *
     * @test
     */
    public function paymentCancelShouldFetchPaymentIfPaymentIdIsPassed(): void
    {
        /** @var MockObject|ResourceService $resourceServiceMock */
        $resourceServiceMock = $this->getMockBuilder(ResourceService::class)->disableOriginalConstructor()->setMethods(['fetchPayment'])->getMock();
        $cancelService = $this->heidelpay->setResourceService($resourceServiceMock)->getCancelService();

        $payment = (new Payment($this->heidelpay))->setId('paymentId');

        /** @noinspection PhpParamsInspection */
        $resourceServiceMock->expects(self::once())->method('fetchPayment')->with('paymentId')->willReturn($payment);
        $cancelService->cancelPayment('paymentId');
    }

    //<editor-fold desc="Data Providers">

    /**
     * @return array
     */
    public function allowedErrorCodesDuringChargeCancel(): array
    {
        return [
            'already cancelled' => [ApiResponseCodes::API_ERROR_ALREADY_CANCELLED, false],
            'already charged' => [ApiResponseCodes::API_ERROR_ALREADY_CHARGED, false],
            'already chargedBack' => [ApiResponseCodes::API_ERROR_ALREADY_CHARGED_BACK, false],
            'other' => [ApiResponseCodes::API_ERROR_BASKET_ITEM_IMAGE_INVALID_URL, true]
        ];
    }

    /**
     * @return array
     */
    public function allowedErrorCodesDuringAuthCancel(): array
    {
        return [
            'already cancelled' => [ApiResponseCodes::API_ERROR_ALREADY_CANCELLED, false],
            'already chargedBack' => [ApiResponseCodes::API_ERROR_ALREADY_CHARGED, false],
            'other' => [ApiResponseCodes::API_ERROR_BASKET_ITEM_IMAGE_INVALID_URL, true]
        ];
    }

    //</editor-fold>
}
