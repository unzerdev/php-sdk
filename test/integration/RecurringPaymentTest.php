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
 * @package  heidelpayPHP/test/integration
 */
namespace heidelpayPHP\test\integration;

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
     * Verify card can activate recurring payments.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function cardShouldBeAbleToActivateRecurringPayments()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $recurring = $card->activateRecurring('https://dev.heidelpay.com');
        $this->assertPending($recurring);
        $this->assertNotEmpty($recurring->getReturnUrl());
    }

    /**
     * Verify paypal can activate recurring payments.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
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
        $recurring = $dd->activateRecurring('https://dev.heidelpay.com');
        $this->assertPending($recurring);
        $this->assertNotEmpty($recurring->getReturnUrl());
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
        $recurring = $ddg->activateRecurring('https://dev.heidelpay.com');
        $this->assertPending($recurring);
        $this->assertNotEmpty($recurring->getReturnUrl());
    }
}
