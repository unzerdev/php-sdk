<?php
/**
 * This class defines integration tests to verify functionality of the Payment charge method.
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
 * @package  heidelpayPHP/test/integration
 */
namespace heidelpayPHP\test\integration;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\TransactionTypes\Cancellation;
use heidelpayPHP\test\BasePaymentTest;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use RuntimeException;

class PaymentCancelTest extends BasePaymentTest
{
    /**
     * Verify full cancel on authorize throws exception if already canceled.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fullCancelOnAuthorizeShouldReturnExistingCancellationIfAlreadyCanceled()
    {
        $authorization = $this->createCardAuthorization();
        $payment = $this->heidelpay->fetchPayment($authorization->getPayment()->getId());
        $cancel = $payment->cancel();
        $this->assertNotNull($cancel);
        $this->assertEquals('s-cnl-1', $cancel->getId());
        $this->assertEquals($authorization->getAmount(), $cancel->getAmount());

        $newCancel = $payment->cancel();
        $this->assertEquals($cancel, $newCancel);
    }

    /**
     * Verify full cancel on charge.
     * PHPLIB-228 - Case 1
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fullCancelOnChargeShouldBePossible()
    {
        $charge = $this->createCharge(123.44);
        $payment = $charge->getPayment();
        $cancellation = $payment->cancel();
        $this->assertTrue($cancellation->getPayment()->isCanceled());
        $this->assertArraySubset([$cancellation], $payment->getCancellations());
        $this->assertEquals($charge->getAmount(), $cancellation->getAmount());
    }

    /**
     * Verify full cancel on multiple charges.
     * PHPLIB-228 - Case 2
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fullCancelOnPaymentWithAuthorizeAndMultipleChargesShouldBePossible()
    {
        $authorization = $this->createCardAuthorization(123.44);
        $payment = $authorization->getPayment();

        $charge1 = $payment->charge(100.44);
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 23.0, 100.44, 123.44, 0);
        $this->assertArraySubset([$charge1], $payment->getCharges());

        $charge2 = $payment->charge(23.00);
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 123.44, 123.44, 0);
        $this->assertArraySubset([$charge1, $charge2], $payment->getCharges());

        $payment->cancel();
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 123.44, 123.44);
        $cancellationTotal = 0.0;
        foreach ($payment->getCancellations() as $cancellation) {
            /** @var Cancellation $cancellation */
            $cancellationTotal += $cancellation->getAmount();
        }
        $this->assertEquals($authorization->getAmount(), $cancellationTotal);
    }

    /**
     * Verify partial cancel on charge.
     * PHPLIB-228 - Case 3
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function partialCancelOnSingleChargeShouldBePossible()
    {
        $charge = $this->createCharge(222.33);
        $this->assertEquals(222.33, $charge->getAmount());

        $payment = $charge->getPayment();
        $this->assertAmounts($payment, 0.0, 222.33, 222.33, 0.0);
        $this->assertTrue($payment->isCompleted());

        $cancel = $charge->cancel(123.12);
        $this->assertEquals(123.12, $cancel->getAmount());

        $this->heidelpay->fetchPayment($payment);
        $this->assertAmounts($payment, 0.0, 99.21, 222.33, 123.12);
        $this->assertTrue($payment->isCompleted());

        $cancel = $charge->cancel(99.21);
        $this->assertEquals(99.21, $cancel->getAmount());

        $this->heidelpay->fetchPayment($payment);
        $this->assertAmounts($payment, 0.0, 0.0, 222.33, 222.33);
        $this->assertTrue($payment->isCanceled());
    }

    /**
     * Verify partial cancel on multiple charges (cancel < last charge).
     * PHPLIB-228 - Case 4 + 5
     *
     * @test
     * @dataProvider partialCancelOnMultipleChargedAuthorizationAmountSmallerThenAuthorizeDP
     *
     * @param $amount
     *
     * @throws AssertionFailedError
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function partialCancelOnMultipleChargedAuthorizationAmountSmallerThenAuthorize($amount)
    {
        $authorizeAmount = 123.44;
        $authorization = $this->createCardAuthorization($authorizeAmount);
        $payment       = $authorization->getPayment();

        $charge1 = $payment->charge(100.44);
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 23.0, 100.44, $authorizeAmount, 0);
        $this->assertArraySubset([$charge1], $payment->getCharges());

        $charge2 = $payment->charge(23.00);
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, $authorizeAmount, $authorizeAmount, 0);
        $this->assertArraySubset([$charge1, $charge2], $payment->getCharges());

        $payment->cancel($amount);
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, $authorizeAmount - $amount, $authorizeAmount, $amount);
        $this->assertCancelledAmount($amount, $payment);
    }

    /**
     * Verify full cancel on authorize.
     * PHPLIB-228 - Case 6
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fullCancelOnAuthorizeShouldBePossible()
    {
        $authorization = $this->createCardAuthorization();
        $fetchedPayment = $this->heidelpay->fetchPayment($authorization->getPayment()->getId());
        $this->assertAmounts($fetchedPayment, 100.0, 0, 100.0, 0);

        $cancel = $fetchedPayment->cancel();
        $this->assertNotNull($cancel);
        $this->assertEquals('s-cnl-1', $cancel->getId());
        $this->assertEquals('100.0', $cancel->getAmount());
        $this->assertAmounts($fetchedPayment, 0.0, 0, 0.0, 0);
        $this->assertTrue($fetchedPayment->isCanceled());
    }

    /**
     * Verify partial cancel on authorize.
     * PHPLIB-228 - Case 7
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fullCancelOnPartCanceledAuthorizeShouldBePossible()
    {
        $authorization = $this->createCardAuthorization();
        $fetchedPayment = $this->heidelpay->fetchPayment($authorization->getPayment()->getId());
        $this->assertAmounts($fetchedPayment, 100.0, 0, 100.0, 0);

        $cancel = $fetchedPayment->cancel(10.0);
        $this->assertNotNull($cancel);
        $this->assertEquals('s-cnl-1', $cancel->getId());
        $this->assertEquals('10.0', $cancel->getAmount());
        $this->assertAmounts($fetchedPayment, 90.0, 0, 90.0, 0);

        $secondCancel = $fetchedPayment->cancel(10.0);
        $this->assertNotNull($secondCancel);
        $this->assertEquals('s-cnl-2', $secondCancel->getId());
        $this->assertEquals('10.0', $secondCancel->getAmount());
        $this->assertAmounts($fetchedPayment, 80.0, 0, 80.0, 0);

        $thirdCancel = $fetchedPayment->cancel();
        $this->assertNotNull($thirdCancel);
        $this->assertEquals('s-cnl-3', $thirdCancel->getId());
        $this->assertEquals('80.0', $thirdCancel->getAmount());
        $this->assertAmounts($fetchedPayment, 0.0, 0, 0.0, 0);
        $this->assertTrue($fetchedPayment->isCanceled());
    }

    /**
     * Verify full cancel on fully charged authorize.
     * PHPLIB-228 - Case 8
     *
     * @test
     * @dataProvider fullCancelOnFullyCanceledAuthorizeShouldBePossibleDP
     *
     * @param float $amount The amount to be cancelled.
     *
     * @throws AssertionFailedError
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fullCancelOnFullyChargedAuthorizeShouldBePossible($amount)
    {
        $authorization = $this->createCardAuthorization();
        $payment = $authorization->getPayment();
        $this->assertAmounts($payment, 100.0, 0, 100.0, 0);
        $this->assertTrue($payment->isPending());

        $payment->charge();
        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 0);
        $this->assertTrue($payment->isCompleted());

        $payment->cancel($amount);
        $this->assertAmounts($payment, 0.0, 0.0, 100.0, 100.0);
        $this->assertTrue($payment->isCanceled());

        $this->assertCancelledAmount(100.0, $payment);
    }

    /**
     * Verify full cancel on partly charged authorize.
     * PHPLIB-228 - Case 8
     *
     * @test
     *
     * @throws AssertionFailedError
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fullCancelOnPartlyChargedAuthorizeShouldBePossible()
    {
        $authorization = $this->createCardAuthorization();
        $payment = $authorization->getPayment();
        $this->assertAmounts($payment, 100.0, 0, 100.0, 0);
        $this->assertTrue($payment->isPending());

        $payment->charge(50.0);
        $this->assertAmounts($payment, 50.0, 50.0, 100.0, 0);
        $this->assertTrue($payment->isPartlyPaid());

        $payment->cancel();
        $this->assertAmounts($payment, 0.0, 0.0, 50.0, 50.0);
        $this->assertTrue($payment->isCanceled());

        $this->assertCancelledAmount(100.0, $payment);
    }

    //<editor-fold desc="Data Providers">

    /**
     * @return array
     */
    public function partialCancelOnMultipleChargedAuthorizationAmountSmallerThenAuthorizeDP(): array
    {
        return [
            'cancel amount lt last charge' => [20],
            'cancel amount gt last charge' => [40]
        ];
    }

    /**
     * @return array
     */
    public function fullCancelOnFullyCanceledAuthorizeShouldBePossibleDP(): array
    {
        return [
            'no amount given' => [null],
            'amount given' => [100.0]
        ];
    }

    //</editor-fold>

    //<editor-fold desc="Helpers">

    /**
     * @param float   $expectedAmount The total amount of all cancellations of the given payment.
     * @param Payment $payment        The payment whose cancellations are to be asserted.
     *
     * @throws Exception
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    private function assertCancelledAmount($expectedAmount, Payment $payment)
    {
        $cancellationTotal = 0.0;
        foreach ($payment->getCancellations() as $cancellation) {
            /** @var Cancellation $cancellation */
            $cancellationTotal += $cancellation->getAmount();
        }
        $this->assertEquals($expectedAmount, $cancellationTotal);
    }

    //</editor-fold>
}
