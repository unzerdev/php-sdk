<?php
/**
 * This class defines integration tests to verify interface and
 * functionality of the Payment resource.
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
 * @package  heidelpay/mgw_sdk/test/integration
 */
namespace heidelpay\MgwPhpSdk\test\integration;

use heidelpay\MgwPhpSdk\Constants\ApiResponseCodes;
use heidelpay\MgwPhpSdk\Constants\Currencies;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;

class PaymentTest extends BasePaymentTest
{
    /**
     * Verify fetching payment by authorization.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws AssertionFailedError
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function paymentShouldBeFetchableById()
    {
        $authorize = $this->createAuthorization();
        $payment = $this->heidelpay->fetchPayment($authorize->getPayment()->getId());
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertNotEmpty($payment->getId());
        $this->assertInstanceOf(Authorization::class, $payment->getAuthorization());
        $this->assertNotEmpty($payment->getAuthorization()->getId());
        $this->assertNotNull($payment->getState());
    }

    /**
     * Verify full charge on payment with authorization.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws AssertionFailedError
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function fullChargeShouldBePossibleOnPaymentObject()
    {
        $authorization = $this->createAuthorization();
        $payment = $authorization->getPayment();

        // pre-check to verify changes due to fullCharge call
        $this->assertAmounts($payment, 100.0, 0.0, 100.0, 0.0);
        $this->assertTrue($payment->isPending());

        /** @var Charge $charge */
        $charge = $payment->charge();
        $paymentNew = $charge->getPayment();

        // verify payment has been updated properly
        $this->assertAmounts($paymentNew, 0.0, 100.0, 100.0, 0.0);
        $this->assertTrue($paymentNew->isCompleted());
    }

    /**
     * Verify payment can be fetched with charges.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function paymentShouldBeFetchableWithCharges()
    {
        $authorize = $this->createAuthorization();
        $payment = $authorize->getPayment();
        $this->assertNotNull($payment);
        $this->assertNotNull($payment->getId());
        $this->assertNotNull($payment->getAuthorization());
        $this->assertNotNull($payment->getAuthorization()->getId());

        $charge = $payment->charge();
        $fetchedPayment = $this->heidelpay->fetchPayment($charge->getPayment()->getId());
        $this->assertNotNull($fetchedPayment->getCharges());
        $this->assertCount(1, $fetchedPayment->getCharges());

        $fetchedCharge = $fetchedPayment->getCharge(0);
        $this->assertEquals($charge->getAmount(), $fetchedCharge->getAmount());
        $this->assertEquals($charge->getCurrency(), $fetchedCharge->getCurrency());
        $this->assertEquals($charge->getId(), $fetchedCharge->getId());
        $this->assertEquals($charge->getReturnUrl(), $fetchedCharge->getReturnUrl());
        $this->assertEquals($charge->expose(), $fetchedCharge->expose());
    }

    /**
     * Verify partial charge after authorization.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function partialChargeAfterAuthorization()
    {
        $authorization = $this->createAuthorization();
        $fetchedPayment = $this->heidelpay->fetchPayment($authorization->getPayment()->getId());
        $charge = $fetchedPayment->charge(10.0);
        $this->assertNotNull($charge);
        $this->assertEquals('s-chg-1', $charge->getId());
        $this->assertEquals('10.0', $charge->getAmount());
    }

    /**
     * Verify full cancel on authorize throws exception if already canceled.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function fullCancelOnAuthorizeShouldThrowExceptionIfAlreadyCanceled()
    {
        $authorization = $this->createAuthorization();
        $fetchedPayment = $this->heidelpay->fetchPayment($authorization->getPayment()->getId());
        $cancel = $fetchedPayment->getAuthorization()->cancel();
        $this->assertNotNull($cancel);
        $this->assertEquals('s-cnl-1', $cancel->getId());
        $this->assertEquals($authorization->getAmount(), $cancel->getAmount());

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CANCELED);
        $fetchedPayment->cancel();
    }

    /**
     * Verify partial cancel on authorize.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function partialCancelOnAuthorizeShouldBePossible()
    {
        $authorization = $this->createAuthorization();
        $fetchedPayment = $this->heidelpay->fetchPayment($authorization->getPayment()->getId());
        $this->assertAmounts($fetchedPayment, 100.0, 0, 100.0, 0);

        $cancel = $fetchedPayment->cancel(10.0);
        $this->assertNotNull($cancel);
        $this->assertEquals('s-cnl-1', $cancel->getId());
        $this->assertEquals('10.0', $cancel->getAmount());
        $this->assertAmounts($fetchedPayment, 90.0, 0, 90.0, 0);
    }

    /**
     * Verify full cancel on charge.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function fullCancelOnChargeShouldBePossible()
    {
        $charge = $this->createCharge();
        $fetchedPayment = $this->heidelpay->fetchPayment($charge->getPayment()->getId());
        $fetchedCharge = $fetchedPayment->getChargeById('s-chg-1');
        $cancellation = $fetchedCharge->cancel();
        $this->assertNotNull($cancellation);
    }

    /**
     * Verify partial cancel on charge.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function partialCancelShouldBePossible()
    {
        $charge = $this->createCharge();
        $fetchedPayment = $this->heidelpay->fetchPayment($charge->getPayment()->getId());
        $cancel = $fetchedPayment->getCharge(0)->cancel(10.0);
        $this->assertNotNull($cancel);
    }

    /**
     * Verify authorization on payment.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws AssertionFailedError
     * @throws ExpectationFailedException
     * @throws \RuntimeException
     */
    public function authorizationShouldBePossibleWithPaymentObject()
    {
        $card = $this->createCardObject();
        $this->heidelpay->createPaymentType($card);

        // Variant 1
        $payment = new Payment($this->heidelpay);
        $authorizationUsingPayment = $payment->authorize(
            100.0,
            Currencies::EURO,
            $card,
            self::RETURN_URL
        );
        $this->assertNotNull($authorizationUsingPayment);
        $this->assertNotEmpty($authorizationUsingPayment->getId());

        // Variant 2
        $authorizationUsingHeidelpay = $this->heidelpay->authorize(
            100.0,
            Currencies::EURO,
            $card,
            self::RETURN_URL
        );

        $this->assertNotNull($authorizationUsingHeidelpay);
        $this->assertNotEmpty($authorizationUsingHeidelpay->getId());
    }
}
