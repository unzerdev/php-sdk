<?php
/**
 * This class defines unit tests to verify cancel functionality of the Payment resource.
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
 * @package  heidelpayPHP/test/unit
 */
namespace heidelpayPHP\test\unit\Resources;

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\TransactionTypes\Authorization;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\test\BaseUnitTest;
use ReflectionException;
use RuntimeException;

class PaymentCancelTest extends BaseUnitTest
{
    //<editor-fold desc="Deprecated since 1.2.3.0">
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
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function cancelShouldThrowExceptionIfNoTransactionExistsToBeCancelled()
    {
        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['cancelAllCharges', 'cancelAuthorization'])->getMock();

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
     *
     * @deprecated since 1.2.3.0
     */
    public function cancelAllChargesShouldCallCancelOnAllChargesAndReturnCancelsAndExceptions()
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

        list($cancellations, $exceptions) = $payment->cancelAllCharges();
        $this->assertArraySubset([$cancellation1, $cancellation2, $cancellation3], $cancellations);
        $this->assertArraySubset([$exception1, $exception2], $exceptions);
    }

    /**
     * Verify cancelAllCharges will throw any exception with Code different to
     * ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CANCELED.
     *
     * @test
     *
     * @throws ReflectionException
     * @throws RuntimeException
     *
     * @deprecated since 1.2.3.0
     */
    public function cancelAllChargesShouldThrowChargeCancelExceptionsOtherThanAlreadyCharged()
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
        $this->assertEquals($cancellation, $paymentMock->cancelAuthorizationAmount());
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
        $exception = new HeidelpayApiException('', '', ApiResponseCodes::API_ERROR_ALREADY_CANCELLED);

        $authorizationMock = $this->getMockBuilder(Authorization::class)->setMethods(['cancel'])->getMock();
        $authorizationMock->expects($this->once())->method('cancel')->willThrowException($exception);

        $paymentMock = $this->getMockBuilder(Payment::class)->setMethods(['getAuthorization'])->getMock();
        $paymentMock->expects($this->once())->method('getAuthorization')->willReturn($authorizationMock);

        /**
         * @var Authorization $authorizationMock
         * @var Payment       $paymentMock
         */
        $paymentMock->setAuthorization($authorizationMock);
        $this->assertNull($paymentMock->cancelAuthorizationAmount());
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
            $paymentMock->cancelAuthorizationAmount();
            $this->assertFalse(true, 'The expected exception has not been thrown.');
        } catch (HeidelpayApiException $e) {
            $this->assertSame($exception, $e);
        }
    }
    //</editor-fold>
}
