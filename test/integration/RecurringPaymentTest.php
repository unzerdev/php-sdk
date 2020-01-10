<?php
/**
 * Test cases to verify functionality and integration of recurring payments.
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

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\PaymentTypes\Card;
use heidelpayPHP\Resources\PaymentTypes\Paypal;
use heidelpayPHP\Resources\PaymentTypes\SepaDirectDebit;
use heidelpayPHP\Resources\PaymentTypes\SepaDirectDebitGuaranteed;
use heidelpayPHP\test\BasePaymentTest;
use PHPUnit\Framework\Exception;
use RuntimeException;

class RecurringPaymentTest extends BasePaymentTest
{
    /**
     * Verify exception is thrown if it is called on a non resource object.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws Exception
     * @throws HeidelpayApiException
     */
    public function exceptionShouldBeThrownIfTheObjectIsNotAResource()
    {
        $resource = new DummyResource();

        $this->expectException(RuntimeException::class);
        $resource->activateRecurring(self::RETURN_URL);
    }

    /**
     * Verify card with 3ds can activate recurring payments.
     * After recurring call the parameters are set.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function recurringForCardWith3dsShouldReturnAttributes()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCardObject()->set3ds(true));
        $recurring = $card->activateRecurring('https://dev.heidelpay.com');
        $this->assertPending($recurring);
        $this->assertEquals('https://dev.heidelpay.com', $recurring->getReturnUrl());
        $this->assertNotEmpty($recurring->getDate());

        $message = $recurring->getMessage();
        $this->assertEquals(ApiResponseCodes::CORE_TRANSACTION_PENDING, $message->getCode());
        $this->assertNotEmpty($message->getCustomer());
    }

    /**
     * Verify card without 3ds can activate recurring payments.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     *
     * @group skip
     */
    public function recurringForCardWithout3dsShouldActivateRecurringAtOnce()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCardObject()->set3ds(false));
        $this->assertFalse($card->isRecurring());

        $recurring = $card->activateRecurring('https://dev.heidelpay.com');
        $this->assertPending($recurring);

        /** @var Card $fetchedCard */
        $fetchedCard = $this->heidelpay->fetchPaymentType($card->getId());
        $this->assertTrue($fetchedCard->isRecurring());
    }

    /**
     * Verify paypal can activate recurring payments.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function paypalShouldBeAbleToActivateRecurringPayments()
    {
        /** @var Paypal $paypal */
        $paypal = $this->heidelpay->createPaymentType(new Paypal());
        $recurring = $paypal->activateRecurring('https://dev.heidelpay.com');
        $this->assertPending($recurring);
        $this->assertNotEmpty($recurring->getReturnUrl());
    }

    /**
     * Verify sepa direct debit can activate recurring payments.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function sepaDirectDebitShouldBeAbleToActivateRecurringPayments()
    {
        /** @var SepaDirectDebit $dd */
        $dd = $this->heidelpay->createPaymentType(new SepaDirectDebit('DE89370400440532013000'));
        $this->assertFalse($dd->isRecurring());
        $dd->charge(10.0, 'EUR', self::RETURN_URL);
        $dd = $this->heidelpay->fetchPaymentType($dd->getId());
        $this->assertTrue($dd->isRecurring());

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_RECURRING_ALREADY_ACTIVE);
        $this->heidelpay->activateRecurringPayment($dd, self::RETURN_URL);
    }

    /**
     * Verify sepa direct debit guaranteed can activate recurring payments.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function sepaDirectDebitGuaranteedShouldBeAbleToActivateRecurringPayments()
    {
        /** @var SepaDirectDebitGuaranteed $ddg */
        $ddg = $this->heidelpay->createPaymentType(new SepaDirectDebitGuaranteed('DE89370400440532013000'));

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_ACTIVATE_RECURRING_VIA_TRANSACTION);
        $ddg->activateRecurring('https://dev.heidelpay.com');
    }
}
