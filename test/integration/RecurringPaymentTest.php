<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
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
use heidelpayPHP\Heidelpay;
use heidelpayPHP\Resources\PaymentTypes\Card;
use heidelpayPHP\Resources\PaymentTypes\Paypal;
use heidelpayPHP\Resources\PaymentTypes\SepaDirectDebit;
use heidelpayPHP\Resources\PaymentTypes\SepaDirectDebitGuaranteed;
use heidelpayPHP\Services\EnvironmentService;
use heidelpayPHP\test\BaseIntegrationTest;
use RuntimeException;

class RecurringPaymentTest extends BaseIntegrationTest
{
    /**
     * Verify exception is thrown if it is called on a non resource object.
     *
     * @test
     */
    public function exceptionShouldBeThrownIfTheObjectIsNotAResource(): void
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
     */
    public function recurringForCardWith3dsShouldReturnAttributes(): void
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
     */
    public function recurringForCardWithout3dsShouldActivateRecurringAtOnce(): void
    {
        $privateKey = EnvironmentService::getTestPrivateKey(true);
        if (empty($privateKey)) {
            $this->markTestIncomplete('No non 3ds private key set');
        }
        $heidelpay = new Heidelpay($privateKey);

        $heidelpay->setDebugMode(true)->setDebugHandler($this->heidelpay->getDebugHandler());

        /** @var Card $card */
        $card = $heidelpay->createPaymentType($this->createCardObject()->set3ds(false));
        $this->assertFalse($card->isRecurring());

        $recurring = $card->activateRecurring('https://dev.heidelpay.com');
        $this->assertSuccess($recurring);

        /** @var Card $fetchedCard */
        $fetchedCard = $heidelpay->fetchPaymentType($card->getId());
        $this->assertTrue($fetchedCard->isRecurring());
    }

    /**
     * Verify paypal can activate recurring payments.
     *
     * @test
     */
    public function paypalShouldBeAbleToActivateRecurringPayments(): void
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
     */
    public function sepaDirectDebitShouldBeAbleToActivateRecurringPayments(): void
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
     */
    public function sepaDirectDebitGuaranteedShouldBeAbleToActivateRecurringPayments(): void
    {
        /** @var SepaDirectDebitGuaranteed $ddg */
        $ddg = $this->heidelpay->createPaymentType(new SepaDirectDebitGuaranteed('DE89370400440532013000'));
        $this->assertFalse($ddg->isRecurring());
        $customer = $this->getMaximumCustomer();
        $customer->setShippingAddress($customer->getBillingAddress());
        $ddg->charge(10.0, 'EUR', self::RETURN_URL, $customer);
        $ddg = $this->heidelpay->fetchPaymentType($ddg->getId());
        $this->assertTrue($ddg->isRecurring());

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_RECURRING_ALREADY_ACTIVE);
        $this->heidelpay->activateRecurringPayment($ddg, self::RETURN_URL);
    }
}
