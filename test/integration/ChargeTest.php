<?php
/**
 * This class defines integration tests to verify charges in general.
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
 * @package  heidelpayPHP/test/integration
 */
namespace heidelpayPHP\test\integration;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\Metadata;
use heidelpayPHP\Resources\Payment;
use heidelpayPHP\Resources\PaymentTypes\Card;
use heidelpayPHP\Resources\PaymentTypes\InvoiceGuaranteed;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class ChargeTest extends BasePaymentTest
{
    /**
     * Verify charge can be performed using the id of a payment type.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function chargeShouldWorkWithTypeId()
    {
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $charge = $this->heidelpay->charge(100.0, 'EUR', $card->getId(), self::RETURN_URL);
        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertNotEmpty($charge->getUniqueId());
        $this->assertNotEmpty($charge->getShortId());
        $this->assertInstanceOf(Payment::class, $charge->getPayment());
        $this->assertNotEmpty($charge->getPayment()->getId());
        $this->assertEquals(self::RETURN_URL, $charge->getReturnUrl());
    }

    /**
     * Verify charging with payment type.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function chargeShouldWorkWithTypeObject()
    {
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $charge = $this->heidelpay->charge(100.0, 'EUR', $card, self::RETURN_URL);
        $this->assertInstanceOf(Charge::class, $charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertInstanceOf(Payment::class, $charge->getPayment());
        $this->assertNotEmpty($charge->getPayment()->getId());
        $this->assertEquals(self::RETURN_URL, $charge->getReturnUrl());
    }

    /**
     * Verify transaction status.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function chargeStatusIsSetCorrectly()
    {
        $charge = $this->createCharge();

        $this->assertTrue($charge->isSuccess());
        $this->assertFalse($charge->isPending());
        $this->assertFalse($charge->isError());
    }

    /**
     * Verify charge accepts all parameters.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function chargeShouldAcceptAllParameters()
    {
        /** @var Card $card */
        $card = $this->heidelpay->createPaymentType($this->createCardObject());
        $customer = $this->getMinimalCustomer();
        $orderId = $this->generateOrderId();
        $metadata = new Metadata();
        $basket = $this->createBasket();
        $invoiceId = $this->generateOrderId();
        $paymentReference = 'paymentReference';

        $charge = $card->charge(100.0, 'EUR', self::RETURN_URL, $customer, $orderId, $metadata, $basket, true, $invoiceId, $paymentReference);
        $payment = $charge->getPayment();

        $this->assertSame($card, $payment->getPaymentType());
        $this->assertEquals(100.0, $charge->getAmount());
        $this->assertEquals('EUR', $charge->getCurrency());
        $this->assertEquals(self::RETURN_URL, $charge->getReturnUrl());
        $this->assertSame($customer, $payment->getCustomer());
        $this->assertEquals($orderId, $charge->getOrderId());
        $this->assertSame($metadata, $payment->getMetadata());
        $this->assertSame($basket, $payment->getBasket());
        $this->assertTrue($charge->isCard3ds());
        $this->assertEquals($invoiceId, $charge->getInvoiceId());
        $this->assertEquals($paymentReference, $charge->getPaymentReference());

        $fetchedCharge = $this->heidelpay->fetchChargeById($charge->getPaymentId(), $charge->getId());
        $fetchedPayment = $fetchedCharge->getPayment();

        $this->assertEquals($payment->getPaymentType()->expose(), $fetchedPayment->getPaymentType()->expose());
        $this->assertEquals($charge->getAmount(), $fetchedCharge->getAmount());
        $this->assertEquals($charge->getCurrency(), $fetchedCharge->getCurrency());
        $this->assertEquals($charge->getReturnUrl(), $fetchedCharge->getReturnUrl());
        $this->assertEquals($payment->getCustomer()->expose(), $fetchedPayment->getCustomer()->expose());
        $this->assertEquals($charge->getOrderId(), $fetchedCharge->getOrderId());
        $this->assertEquals($payment->getMetadata()->expose(), $fetchedPayment->getMetadata()->expose());
        $this->assertEquals($payment->getBasket()->expose(), $fetchedPayment->getBasket()->expose());
        $this->assertEquals($charge->isCard3ds(), $fetchedCharge->isCard3ds());
        $this->assertEquals($charge->getInvoiceId(), $fetchedCharge->getInvoiceId());
        $this->assertEquals($charge->getPaymentReference(), $fetchedCharge->getPaymentReference());
    }

    /**
     * Verify charge accepts all parameters.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function chargeWithCustomerShouldAcceptAllParameters()
    {
        /** @var InvoiceGuaranteed $ivg */
        $ivg = $this->heidelpay->createPaymentType(new InvoiceGuaranteed());
        $customer = $this->getMaximumCustomer();
        $customer->setShippingAddress($customer->getBillingAddress());
        $orderId = $this->generateOrderId();
        $metadata = new Metadata();
        $basket = $this->createBasket();
        $invoiceId = $this->generateOrderId();
        $paymentReference = 'paymentReference';

        $charge = $ivg->charge(100.0, 'EUR', self::RETURN_URL, $customer, $orderId, $metadata, $basket, null, $invoiceId, $paymentReference);
        $payment = $charge->getPayment();

        $this->assertSame($ivg, $payment->getPaymentType());
        $this->assertEquals(100.0, $charge->getAmount());
        $this->assertEquals('EUR', $charge->getCurrency());
        $this->assertEquals(self::RETURN_URL, $charge->getReturnUrl());
        $this->assertSame($customer, $payment->getCustomer());
        $this->assertEquals($orderId, $charge->getOrderId());
        $this->assertSame($metadata, $payment->getMetadata());
        $this->assertSame($basket, $payment->getBasket());
        $this->assertEquals($invoiceId, $charge->getInvoiceId());
        $this->assertEquals($paymentReference, $charge->getPaymentReference());

        $fetchedCharge = $this->heidelpay->fetchChargeById($charge->getPaymentId(), $charge->getId());
        $this->assertEquals($charge->expose(), $fetchedCharge->expose());
    }
}
