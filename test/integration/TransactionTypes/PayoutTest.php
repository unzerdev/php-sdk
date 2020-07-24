<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
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
 * @package  heidelpayPHP\test\integration\TransactionTypes
 */
namespace heidelpayPHP\test\integration\TransactionTypes;

use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\Card;
use heidelpayPHP\Resources\PaymentTypes\SepaDirectDebit;
use heidelpayPHP\Resources\PaymentTypes\SepaDirectDebitGuaranteed;
use heidelpayPHP\Resources\TransactionTypes\Payout;
use heidelpayPHP\Services\ResourceService;
use heidelpayPHP\test\BaseIntegrationTest;

class PayoutTest extends BaseIntegrationTest
{
    /**
     * Verify payout can be performed for card payment type.
     *
     * @test
     */
    public function payoutCanBeCalledForCardType(): void
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $payout = $card->payout(100.0, 'EUR', self::RETURN_URL);
        $this->assertTransactionResourceHasBeenCreated($payout);

        $payment = $payout->getPayment();
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertNotEmpty($payment->getId());
        $this->assertEquals(self::RETURN_URL, $payout->getReturnUrl());

        $this->assertAmounts($payment, 0, 0, -100, 0);

        $traceId = $payout->getTraceId();
        $this->assertNotEmpty($traceId);
        $this->assertSame($traceId, $payout->getPayment()->getTraceId());
    }

    /**
     * Verify payout can be performed for sepa direct debit payment type.
     *
     * @test
     */
    public function payoutCanBeCalledForSepaDirectDebitType(): void
    {
        $sepa = new SepaDirectDebit('DE89370400440532013000');
        $this->heidelpay->createPaymentType($sepa);
        $payout = $sepa->payout(100.0, 'EUR', self::RETURN_URL);
        $this->assertTransactionResourceHasBeenCreated($payout);

        $payment = $payout->getPayment();
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertNotEmpty($payment->getId());
        $this->assertEquals(self::RETURN_URL, $payout->getReturnUrl());
        $this->assertAmounts($payment, 0, 0, -100, 0);
    }

    /**
     * Verify payout can be performed for sepa direct debit guaranteed payment type.
     *
     * @test
     */
    public function payoutCanBeCalledForSepaDirectDebitGuaranteedType(): void
    {
        $sepa = new SepaDirectDebitGuaranteed('DE89370400440532013000');
        $this->heidelpay->createPaymentType($sepa);
        $customer = $this->getMaximumCustomer()->setShippingAddress($this->getBillingAddress());
        $payout   = $sepa->payout(100.0, 'EUR', self::RETURN_URL, $customer);
        $this->assertTransactionResourceHasBeenCreated($payout);

        $payment = $payout->getPayment();
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertNotEmpty($payment->getId());
        $this->assertEquals(self::RETURN_URL, $payout->getReturnUrl());
        $this->assertAmounts($payment, 0, 0, -100, 0);
    }
    
    /**
     * Verify Payout transaction is fetched with Payment resource.
     *
     * @test
     */
    public function payoutShouldBeFetchedWhenItsPaymentResourceIsFetched(): void
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

    /**
     * Verify Payout can be fetched via url.
     *
     * @test
     */
    public function payoutShouldBeFetchableViaItsUrl(): void
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $payout = $card->payout(100.0, 'EUR', self::RETURN_URL);

        $resourceSrv = new ResourceService($this->heidelpay);
        $fetchedPayout = $resourceSrv->fetchResourceByUrl($payout->getUri());
        $this->assertEquals($payout->expose(), $fetchedPayout->expose());
    }

    /**
     * Verify payout accepts all parameters.
     *
     * @test
     */
    public function payoutShouldAcceptAllParameters(): void
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $customer = $this->getMinimalCustomer();
        $orderId = 'o'. self::generateRandomId();
        $metadata = (new Metadata())->addMetadata('key', 'value');
        $basket = $this->createBasket();
        $invoiceId = 'i'. self::generateRandomId();
        $paymentReference = 'paymentReference';

        $payout = $card->payout(119.0, 'EUR', self::RETURN_URL, $customer, $orderId, $metadata, $basket, $invoiceId, $paymentReference);
        $payment = $payout->getPayment();

        $this->assertSame($card, $payment->getPaymentType());
        $this->assertEquals(119.0, $payout->getAmount());
        $this->assertEquals('EUR', $payout->getCurrency());
        $this->assertEquals(self::RETURN_URL, $payout->getReturnUrl());
        $this->assertSame($customer, $payment->getCustomer());
        $this->assertEquals($orderId, $payout->getOrderId());
        $this->assertSame($metadata, $payment->getMetadata());
        $this->assertSame($basket, $payment->getBasket());
        $this->assertEquals($invoiceId, $payout->getInvoiceId());
        $this->assertEquals($paymentReference, $payout->getPaymentReference());

        $fetchedPayout = $this->heidelpay->fetchPayout($payout->getPaymentId());
        $fetchedPayment = $fetchedPayout->getPayment();

        $this->assertEquals($payment->getPaymentType()->expose(), $fetchedPayment->getPaymentType()->expose());
        $this->assertEquals($payout->getAmount(), $fetchedPayout->getAmount());
        $this->assertEquals($payout->getCurrency(), $fetchedPayout->getCurrency());
        $this->assertEquals($payout->getReturnUrl(), $fetchedPayout->getReturnUrl());
        $this->assertEquals($payment->getCustomer()->expose(), $fetchedPayment->getCustomer()->expose());
        $this->assertEquals($payout->getOrderId(), $fetchedPayout->getOrderId());
        $this->assertEquals($payment->getMetadata()->expose(), $fetchedPayment->getMetadata()->expose());
        $this->assertEquals($payment->getBasket()->expose(), $fetchedPayment->getBasket()->expose());
        $this->assertEquals($payout->getInvoiceId(), $fetchedPayout->getInvoiceId());
        $this->assertEquals($payout->getPaymentReference(), $fetchedPayout->getPaymentReference());
    }
}
