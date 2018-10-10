<?php
/**
 * This class defines integration tests to verify interface and
 * functionality of the Payment resource.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/tests/integration
 */
namespace heidelpay\MgwPhpSdk\test\integration;

use heidelpay\MgwPhpSdk\Constants\ApiResponseCodes;
use heidelpay\MgwPhpSdk\Exceptions\HeidelpayApiException;
use heidelpay\MgwPhpSdk\Resources\Payment;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Authorization;
use heidelpay\MgwPhpSdk\Resources\TransactionTypes\Charge;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;

class PaymentTest extends BasePaymentTest
{
    /**
     * Verify fetching payment by authorization.
     *
     * @test
     */
    public function PaymentShouldBeFetchableById()
    {
        $authorize = $this->createAuthorization();
        $payment = $this->heidelpay->fetchPaymentById($authorize->getPayment()->getId());
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
     * Verify full charge on payment with authorization.
     *
     * @test
     */
    public function moreThanOneChargeShouldBePossibleOnPaymentObject()
    {
        $charge = $this->createCharge();
        $payment = $charge->getPayment();

        $this->assertAmounts($payment, 0.0, 100.0, 100.0, 0.0);
        $this->assertTrue($payment->isCompleted());

        /** @var Charge $charge */
        $charge = $payment->charge(100.0);
        $paymentNew = $charge->getPayment();

        // verify payment has been updated properly
        $this->assertAmounts($paymentNew, 0.0, 200.0, 200.0, 0.0);
        $this->assertTrue($paymentNew->isCompleted());
    }

    /**
     * Verify payment can be fetched with charges.
     *
     * @test
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
		$fetchedPayment = $this->heidelpay->fetchPaymentById($charge->getPayment()->getId());
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
     */
    public function partialChargeAfterAuthorization()
    {
        $authorization = $this->createAuthorization();
        $fetchedPayment = $this->heidelpay->fetchPaymentById($authorization->getPayment()->getId());
        $charge = $fetchedPayment->charge(10.0);
		$this->assertNotNull($charge);
		$this->assertEquals('s-chg-1', $charge->getId());
		$this->assertEquals('10.0', $charge->getAmount());
    }

    /**
     * Verify full cancel on authorize throws exception if already canceled.
     *
     * @test
     */
    public function fullCancelOnAuthorizeShouldThrowExceptionIfAlreadyCanceled()
    {
        $authorization = $this->createAuthorization();
        $fetchedPayment = $this->heidelpay->fetchPaymentById($authorization->getPayment()->getId());
		$cancel = $fetchedPayment->getAuthorization()->cancel();
		$this->assertNotNull($cancel);
		$this->assertEquals('s-cnl-1', $cancel->getId());
		$this->assertEquals($authorization->getAmount(), $cancel->getAmount());

		$this->expectException(HeidelpayApiException::class);
		$this->expectExceptionCode(ApiResponseCodes::API_ERROR_CHARGE_ALREADY_CANCELED);
        $fetchedPayment->cancel();
    }
}
