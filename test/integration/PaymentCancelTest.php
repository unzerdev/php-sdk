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
use heidelpayPHP\Resources\PaymentTypes\Invoice;
use heidelpayPHP\test\BasePaymentTest;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use RuntimeException;

class PaymentCancelTest extends BasePaymentTest
{
    /**
     * Verify full cancel on authorize returns first cancellation if already cancelled.
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
        $cancel = $payment->cancel(); // todo: array mit den erzeugten cancellations
        $this->assertTrue($payment->isCanceled());
        $this->assertEquals($authorization->getAmount(), $cancel->getAmount());

        $newCancel = $payment->cancel(); // todo: leeres array
        $this->assertEquals($cancel, $newCancel);
    }

    /**
     * Return first cancel if charge is already fully cancelled.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function doubleCancelOnChargeShouldReturnFirstCancel()
    {
        $charge = $this->createCharge(123.44);
        $payment = $charge->getPayment();
        $cancellation = $payment->cancel();
        $this->assertTrue($payment->isCanceled()); // todo: array mit den erzeugten cancellations
        $this->assertArraySubset([$cancellation], $payment->getCancellations());
        $this->assertEquals($charge->getAmount(), $cancellation->getAmount());

        $newCancellation = $payment->cancel(); // todo: leeres array
        $this->assertEquals($cancellation, $newCancellation);
        $this->assertEquals($charge->getAmount(), $cancellation->getAmount());
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
        $this->assertAmounts($payment, 0, 123.44, 123.44, 0);

        $payment->cancel();
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0, 0, 123.44, 123.44);
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
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 123.44, 0.0, 123.44, 0);

        $payment->charge(100.44);
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 23.0, 100.44, 123.44, 0);

        $payment->charge(23.00);
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 123.44, 123.44, 0);

        $payment->cancel();
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 123.44, 123.44);
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
        $payment = $charge->getPayment();
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 222.33, 222.33, 0.0);

        $charge->cancel(123.12);
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 99.21, 222.33, 123.12);

        $payment->cancel(99.21);
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 222.33, 222.33);
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

        $payment->charge(100.44);
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 23.0, 100.44, $authorizeAmount, 0);

        $payment->charge(23.00);
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, $authorizeAmount, $authorizeAmount, 0);

        $payment->cancel($amount);
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, $authorizeAmount - $amount, $authorizeAmount, $amount);
    }

    /**
     * Verify full cancel on authorize.
     * PHPLIB-228 - Case 6
     *
     * @test
     * @dataProvider fullCancelDataProvider
     *
     * @param float $amount
     *
     * @throws AssertionFailedError
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fullCancelOnAuthorizeShouldBePossible($amount)
    {
        $authorization = $this->createCardAuthorization();
        $payment = $authorization->getPayment();
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0, 100.0, 0);

        $payment->cancel($amount);
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0, 0.0, 0);
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
        $payment = $authorization->getPayment();
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0, 100.0, 0);

        $payment->cancel(10.0);
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 90.0, 0, 90.0, 0);

        $payment->cancel(10.0);
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 80.0, 0, 80.0, 0);

        $payment->cancel();
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0, 0.0, 0);
    }

    /**
     * Verify full cancel on fully charged authorize.
     * PHPLIB-228 - Case 8
     *
     * @test
     * @dataProvider fullCancelDataProvider
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
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0, 100.0, 0);

        $payment->charge();
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 0);

        $payment->cancel($amount);
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 100.0, 100.0);
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
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0, 100.0, 0);

        $payment->charge(50.0);
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 50.0, 50.0, 100.0, 0);

        $payment->cancel();
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 50.0, 50.0);
    }

    /**
     * Verify full cancel on initial iv charge (reversal)
     *
     * @test
     * @dataProvider fullCancelDataProvider
     *
     * @param float $amount
     * @throws AssertionFailedError
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fullCancelOnInitialInvoiceChargeShouldBePossible($amount)
    {
        /** @var Invoice $invoice */
        $invoice = $this->heidelpay->createPaymentType(new Invoice());
        $charge = $invoice->charge(100.0, 'EUR', self::RETURN_URL);
        $payment = $charge->getPayment();
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0, 100.0, 0);

        $payment->cancel($amount);
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 0.0, 0.0);
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
    public function fullCancelDataProvider(): array
    {
        return [
            'no amount given' => [null],
            'amount given' => [100.0]
        ];
    }

    //</editor-fold>
}
