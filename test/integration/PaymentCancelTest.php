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
 * @package  heidelpayPHP\test\integration
 */
namespace heidelpayPHP\test\integration;

use heidelpayPHP\Constants\CancelReasonCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\PaymentTypes\Invoice;
use heidelpayPHP\test\BasePaymentTest;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use RuntimeException;

class PaymentCancelTest extends BasePaymentTest
{
    //<editor-fold desc="Tests">

    /**
     * Verify full cancel on cancelled authorize returns empty array.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function doubleCancelOnAuthorizeShouldReturnEmptyArray()
    {
        $authorization = $this->createCardAuthorization();
        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);

        $cancellations = $payment->cancelAmount();
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 0.0, 0.0);
        $this->assertCount(1, $cancellations);

        $newCancellations = $payment->cancelAmount();
        $this->assertCount(0, $newCancellations);
    }

    /**
     * Verify full cancel on charge.
     * AND
     * Return empty array if charge is already fully cancelled.
     * PHPLIB-228 - Case 1 + double cancel
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function cancelOnChargeAndDoubleCancel()
    {
        $charge = $this->createCharge(123.44);
        $payment = $this->heidelpay->fetchPayment($charge->getPaymentId());
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 123.44, 123.44, 0.0);

        $cancellations = $payment->cancelAmount();
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 123.44, 123.44);
        $this->assertCount(1, $cancellations);

        $payment = $this->heidelpay->fetchPayment($charge->getPaymentId());
        $newCancellations = $payment->cancelAmount();
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 123.44, 123.44);
        $this->assertCount(0, $newCancellations);
    }

    /**
     * Verify full cancel on multiple charges.
     * PHPLIB-228 - Case 2
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function fullCancelOnPaymentWithAuthorizeAndMultipleChargesShouldBePossible()
    {
        $authorization = $this->createCardAuthorization(123.44);
        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 123.44, 0.0, 123.44, 0.0);

        $charge1 = $payment->charge(100.44);
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 23.0, 100.44, 123.44, 0.0);

        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $charge2 = $payment->charge(23.00);
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 123.44, 123.44, 0.0);

        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $this->assertCount(2, $payment->cancelAmount());
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 123.44, 123.44);

        $allCancellations = $payment->getCancellations();
        $this->assertCount(2, $allCancellations);
        $this->assertEquals($charge1->getId(), $allCancellations[0]->getParentResource()->getId());
        $this->assertEquals($charge2->getId(), $allCancellations[1]->getParentResource()->getId());
    }

    /**
     * Verify partial cancel on charge.
     * PHPLIB-228 - Case 3
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function partialCancelAndFullCancelOnSingleCharge()
    {
        $charge = $this->createCharge(222.33);
        $payment = $this->heidelpay->fetchPayment($charge->getPaymentId());
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 222.33, 222.33, 0.0);

        $this->assertCount(1, $payment->cancelAmount(123.12));
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 99.21, 222.33, 123.12);

        $payment = $this->heidelpay->fetchPayment($charge->getPaymentId());
        $this->assertCount(1, $payment->cancelAmount(99.21));
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 222.33, 222.33);
    }

    /**
     * Verify partial cancel on multiple charges (cancel < last charge).
     * PHPLIB-228 - Case 4 + 5
     *
     * @test
     * @dataProvider partCancelDataProvider
     *
     * @param float $amount
     * @param int   $numberCancels
     *
     * @throws AssertionFailedError
     * @throws Exception
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function partialCancelOnMultipleChargedAuthorization($amount, $numberCancels)
    {
        $authorizeAmount = 123.44;
        $authorization = $this->createCardAuthorization($authorizeAmount);
        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());

        $payment->charge(23.00);
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 100.44, 23.0, $authorizeAmount, 0.0);

        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $payment->charge(100.44);
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, $authorizeAmount, $authorizeAmount, 0.0);

        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $this->assertCount($numberCancels, $payment->cancelAmount($amount));
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
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function fullCancelOnAuthorize($amount)
    {
        $authorization = $this->createCardAuthorization();
        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);

        $this->assertCount(1, $payment->cancelAmount($amount));
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 0.0, 0.0);
    }

    /**
     * Verify partial cancel on authorize.
     * PHPLIB-228 - Case 7
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function fullCancelOnPartCanceledAuthorize()
    {
        $authorization = $this->createCardAuthorization();
        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);

        $this->assertCount(1, $payment->cancelAmount(10.0));
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 90.0, 0.0, 90.0, 0.0);

        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $this->assertCount(1, $payment->cancelAmount(10.0));
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 80.0, 0.0, 80.0, 0.0);

        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $this->assertCount(1, $payment->cancelAmount());
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 0.0, 0.0);
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
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function fullCancelOnFullyChargedAuthorize($amount)
    {
        $authorization = $this->createCardAuthorization();
        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);

        $payment->charge();
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 0.0);

        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $this->assertCount(1, $payment->cancelAmount($amount));
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 100.0, 100.0);
    }

    /**
     * Verify full cancel on partly charged authorize.
     * PHPLIB-228 - Case 9
     *
     * @test
     * @dataProvider fullCancelDataProvider
     *
     * @param $amount
     *
     * @throws AssertionFailedError
     * @throws Exception
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function fullCancelOnPartlyChargedAuthorizeShouldBePossible($amount)
    {
        $authorization = $this->createCardAuthorization();
        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);

        $payment->charge(50.0);
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 50.0, 50.0, 100.0, 0.0);

        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $this->assertCount(2, $payment->cancelAmount($amount));
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 50.0, 50.0);
    }

    /**
     * Verify part cancel on uncharged authorize.
     * PHPLIB-228 - Case 10
     *
     * @test
     *
     * @throws AssertionFailedError
     * @throws Exception
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function partCancelOnUnchargedAuthorize()
    {
        $authorization = $this->createCardAuthorization();
        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);

        $this->assertCount(1, $payment->cancelAmount(50.0));
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 50.0, 0.0, 50.0, 0.0);
    }

    /**
     * Verify part cancel on partly charged authorize with cancel amount lt charged amount.
     * PHPLIB-228 - Case 11
     *
     * @test
     *
     * @throws AssertionFailedError
     * @throws Exception
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function partCancelOnPartlyChargedAuthorizeWithAmountLtCharged()
    {
        $authorization = $this->createCardAuthorization();
        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);

        $payment->charge(25.0);
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 75.0, 25.0, 100.0, 0.0);

        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $this->assertCount(1, $payment->cancelAmount(20.0));
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 55.0, 25.0, 80.0, 0.0);
    }

    /**
     * Verify part cancel on partly charged authorize with cancel amount gt charged amount.
     * PHPLIB-228 - Case 12
     *
     * @test
     *
     * @throws AssertionFailedError
     * @throws Exception
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function partCancelOnPartlyChargedAuthorizeWithAmountGtCharged()
    {
        $authorization = $this->createCardAuthorization();
        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);

        $payment->charge(40.0);
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 60.0, 40.0, 100.0, 0.0);

        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $this->assertCount(2, $payment->cancelAmount(80.0));
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 20.0, 40.0, 20.0);
    }

    /**
     * Verify full cancel on initial iv charge (reversal)
     * PHPLIB-228 - Case 13
     *
     * @test
     * @dataProvider fullCancelDataProvider
     *
     * @param float $amount
     *
     * @throws AssertionFailedError
     * @throws Exception
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function fullCancelOnInitialInvoiceCharge($amount)
    {
        /** @var Invoice $invoice */
        $invoice = $this->heidelpay->createPaymentType(new Invoice());
        $charge = $invoice->charge(100.0, 'EUR', self::RETURN_URL);
        $payment = $this->heidelpay->fetchPayment($charge->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);

        $this->assertCount(1, $payment->cancelAmount($amount));
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 0.0, 0.0);
    }

    /**
     * Verify part cancel on initial iv charge (reversal)
     * PHPLIB-228 - Case 14
     *
     * @test
     *
     * @throws AssertionFailedError
     * @throws Exception
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function partCancelOnInitialInvoiceChargeShouldBePossible()
    {
        /** @var Invoice $invoice */
        $invoice = $this->heidelpay->createPaymentType(new Invoice());
        $charge = $invoice->charge(100.0, 'EUR', self::RETURN_URL);
        $payment = $this->heidelpay->fetchPayment($charge->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);

        $this->assertCount(1, $payment->cancelAmount(50.0));
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 50.0, 0.0, 50.0, 0.0);
    }

    /**
     * Verify cancelling more than was charged.
     * PHPLIB-228 - Case 15
     *
     * @test
     *
     * @throws AssertionFailedError
     * @throws Exception
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function cancelMoreThanWasCharged()
    {
        $charge = $this->createCharge(50.0);
        $payment = $this->heidelpay->fetchPayment($charge->getPaymentId());
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 50.0, 50.0, 0.0);

        $this->assertCount(1, $payment->cancelAmount(100.0));
        $this->assertTrue($payment->isCanceled());
        $this->assertAmounts($payment, 0.0, 0.0, 50.0, 50.0);
    }

    /**
     * Verify second cancel on partly cancelled charge.
     * PHPLIB-228 - Case 16
     *
     * @test
     *
     * @throws AssertionFailedError
     * @throws Exception
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function secondCancelExceedsRemainderOfPartlyCancelledCharge()
    {
        $authorization = $this->createCardAuthorization();
        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $this->assertTrue($payment->isPending());
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);

        $payment->charge(50.0);
        $this->assertTrue($payment->isPartlyPaid());
        $this->assertAmounts($payment, 50.0, 50.0, 100.0, 0.0);

        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $payment->charge(50.0);
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 0.0);

        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $this->assertCount(1, $payment->cancelAmount(40.0));
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 60.0, 100.0, 40.0);

        $payment = $this->heidelpay->fetchPayment($authorization->getPaymentId());
        $this->assertCount(2, $payment->cancelAmount(30.0));
        $this->assertTrue($payment->isCompleted());
        $this->assertAmounts($payment, 0.0, 30.0, 100.0, 70.0);
    }

    /**
     * Verify cancellation with all parameters set.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function cancellationShouldWorkWithAllParametersSet()
    {
        $authorization = $this->createCardAuthorization(119.0);
        $payment = $authorization->getPayment();
        $payment->charge();
        $cancellations = $payment->cancelAmount(59.5, CancelReasonCodes::REASON_CODE_CREDIT, 'Reference text!', 50.0, 9.5);
        $this->assertCount(1, $cancellations);
    }

    //</editor-fold>

    //<editor-fold desc="Data Providers">

    /**
     * @return array
     */
    public function partCancelDataProvider(): array
    {
        return [
            'cancel amount lt last charge' => [20, 1],
            'cancel amount eq to last charge' => [23, 1],
            'cancel amount gt last charge' => [40, 2]
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
