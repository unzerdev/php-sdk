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

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\test\BasePaymentTest;
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
    public function fullCancelOnAuthorizeShouldThrowExceptionIfAlreadyCanceled()
    {
        $authorization = $this->createCardAuthorization();
        $fetchedPayment = $this->heidelpay->fetchPayment($authorization->getPayment()->getId());
        $cancel = $fetchedPayment->getAuthorization()->cancel();
        $this->assertNotNull($cancel);
        $this->assertEquals('s-cnl-1', $cancel->getId());
        $this->assertEquals($authorization->getAmount(), $cancel->getAmount());

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_AUTHORIZE_ALREADY_CANCELLED);
        $fetchedPayment->cancel();
    }

    /**
     * Verify full cancel on authorize.
     * Case 6
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
     * Case 7
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
     * Verify full cancel on charge.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function fullCancelOnChargeShouldBePossible()
    {
        $charge = $this->createCharge();
        $fetchedPayment = $this->heidelpay->fetchPayment($charge->getPayment()->getId());
        $fetchedCharge = $fetchedPayment->getCharge('s-chg-1');
        $cancellation = $fetchedCharge->cancel();
        $this->assertNotNull($cancellation);
    }

    /**
     * Verify partial cancel on charge.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function partialCancelShouldBePossible()
    {
        $charge = $this->createCharge();
        $fetchedPayment = $this->heidelpay->fetchPayment($charge->getPayment()->getId());
        $cancel = $fetchedPayment->getChargeByIndex(0)->cancel(10.0);
        $this->assertNotNull($cancel);
    }
}
