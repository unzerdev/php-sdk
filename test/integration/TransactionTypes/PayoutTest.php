<?php
/**
 * This class defines integration tests to verify payout transactions.
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
 * @package  heidelpayPHP/test/integration/transaction_types
 */
namespace heidelpayPHP\test\integration\TransactionTypes;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\Card;
use heidelpayPHP\Resources\PaymentTypes\SepaDirectDebit;
use heidelpayPHP\Resources\PaymentTypes\SepaDirectDebitGuaranteed;
use heidelpayPHP\Resources\TransactionTypes\Payout;
use heidelpayPHP\test\BasePaymentTest;
use PHPUnit\Framework\Exception;
use RuntimeException;

class PayoutTest extends BasePaymentTest
{
    /**
     * Verify payout can be performed for card payment type.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function payoutCanBeCalledForCardType()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $payout = $card->payout(100.0, 'EUR', self::RETURN_URL);
        $this->assertNotEmpty($payout->getId());
        $this->assertNotEmpty($payout->getUniqueId());
        $this->assertNotEmpty($payout->getShortId());

        $payment = $payout->getPayment();
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertNotEmpty($payment->getId());
        $this->assertEquals(self::RETURN_URL, $payout->getReturnUrl());
        $amount = $payment->getAmount();

        $this->assertEquals(-100, $amount->getTotal());
        $this->assertEquals(0, $amount->getCharged());
        $this->assertEquals(0, $amount->getCanceled());
        $this->assertEquals(0, $amount->getRemaining());
    }

    /**
     * Verify payout can be performed for sepa direct debit payment type.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function payoutCanBeCalledForSepaDirectDebitType()
    {
        $sepa = new SepaDirectDebit('DE89370400440532013000');
        $this->heidelpay->createPaymentType($sepa);
        $payout = $sepa->payout(100.0, 'EUR', self::RETURN_URL);
        $this->assertNotEmpty($payout->getId());
        $this->assertNotEmpty($payout->getUniqueId());
        $this->assertNotEmpty($payout->getShortId());

        $payment = $payout->getPayment();
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertNotEmpty($payment->getId());
        $this->assertEquals(self::RETURN_URL, $payout->getReturnUrl());
        $amount = $payment->getAmount();

        $this->assertEquals(-100, $amount->getTotal());
        $this->assertEquals(0, $amount->getCharged());
        $this->assertEquals(0, $amount->getCanceled());
        $this->assertEquals(0, $amount->getRemaining());
    }

    /**
     * Verify payout can be performed for sepa direct debit guaranteed payment type.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function payoutCanBeCalledForSepaDirectDebitGuaranteedType()
    {
        $sepa = new SepaDirectDebitGuaranteed('DE89370400440532013000');
        $this->heidelpay->createPaymentType($sepa);
        $customer = $this->getMaximumCustomer()->setShippingAddress($this->getBillingAddress());
        $payout   = $sepa->payout(100.0, 'EUR', self::RETURN_URL, $customer);
        $this->assertNotEmpty($payout->getId());
        $this->assertNotEmpty($payout->getUniqueId());
        $this->assertNotEmpty($payout->getShortId());

        $payment = $payout->getPayment();
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertNotEmpty($payment->getId());
        $this->assertEquals(self::RETURN_URL, $payout->getReturnUrl());
        $amount = $payment->getAmount();

        $this->assertEquals(-100, $amount->getTotal());
        $this->assertEquals(0, $amount->getCharged());
        $this->assertEquals(0, $amount->getCanceled());
        $this->assertEquals(0, $amount->getRemaining());
    }
    
    /**
     * Verify Payout transaction is fetched with Payment resource.
     *
     * @test
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws Exception
     */
    public function payoutShouldBeFetchedWhenItsPaymentResourceIsFetched()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $payout = $card->payout(100.0, 'EUR', self::RETURN_URL);

        $fetchedPayment = $this->heidelpay->fetchPayment($payout->getPaymentId());
        $this->assertInstanceOf(Payout::class, $fetchedPayment->getPayout());
        $this->assertEquals(100, $payout->getAmount());
        $this->assertEquals('EUR', $payout->getCurrency());
        $this->assertEquals(self::RETURN_URL, $payout->getReturnUrl());
    }
}
