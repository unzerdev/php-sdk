<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
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
use heidelpayPHP\Resources\PaymentTypes\InvoiceGuaranteed;
use heidelpayPHP\Resources\PaymentTypes\SepaDirectDebit;
use heidelpayPHP\test\BaseIntegrationTest;

class ChargeTest extends BaseIntegrationTest
{
    /**
     * Verify charge can be performed using the id of a payment type.
     *
     * @test
     */
    public function chargeShouldWorkWithTypeId(): void
    {
        $paymentType = $this->heidelpay->createPaymentType(new SepaDirectDebit('DE89370400440532013000'));
        $charge = $this->heidelpay->charge(100.0, 'EUR', $paymentType->getId(), self::RETURN_URL);
        $this->assertTransactionResourceHasBeenCreated($charge);
        $this->assertInstanceOf(Payment::class, $charge->getPayment());
        $this->assertNotEmpty($charge->getPayment()->getId());
        $this->assertEquals(self::RETURN_URL, $charge->getReturnUrl());
    }

    /**
     * Verify charging with payment type.
     *
     * @test
     */
    public function chargeShouldWorkWithTypeObject(): void
    {
        $paymentType = $this->heidelpay->createPaymentType(new SepaDirectDebit('DE89370400440532013000'));
        $charge = $this->heidelpay->charge(100.0, 'EUR', $paymentType, self::RETURN_URL);
        $this->assertTransactionResourceHasBeenCreated($charge);
        $this->assertInstanceOf(Payment::class, $charge->getPayment());
        $this->assertNotEmpty($charge->getPayment()->getId());
        $this->assertEquals(self::RETURN_URL, $charge->getReturnUrl());
    }

    /**
     * Verify transaction status.
     *
     * @test
     */
    public function chargeStatusIsSetCorrectly(): void
    {
        $this->assertSuccess($this->createCharge());
    }

    /**
     * Verify charge accepts all parameters.
     *
     * @test
     */
    public function chargeShouldAcceptAllParameters(): void
    {
        // prepare test data
        /** @var Card $paymentType */
        $paymentType = $this->heidelpay->createPaymentType($this->createCardObject());
        $customer = $this->getMinimalCustomer();
        $orderId = 'o'. self::generateRandomId();
        $metadata = (new Metadata())->addMetadata('key', 'value');
        $basket = $this->createBasket();
        $invoiceId = 'i'. self::generateRandomId();
        $paymentReference = 'paymentReference';

        // perform request
        $charge = $paymentType->charge(119.0, 'EUR', self::RETURN_URL, $customer, $orderId, $metadata, $basket, true, $invoiceId, $paymentReference);

        // verify the data sent and received match
        $payment = $charge->getPayment();
        $this->assertSame($paymentType, $payment->getPaymentType());
        $this->assertEquals(119.0, $charge->getAmount());
        $this->assertEquals('EUR', $charge->getCurrency());
        $this->assertEquals(self::RETURN_URL, $charge->getReturnUrl());
        $this->assertSame($customer, $payment->getCustomer());
        $this->assertEquals($orderId, $charge->getOrderId());
        $this->assertSame($metadata, $payment->getMetadata());
        $this->assertSame($basket, $payment->getBasket());
        $this->assertTrue($charge->isCard3ds());
        $this->assertEquals($invoiceId, $charge->getInvoiceId());
        $this->assertEquals($paymentReference, $charge->getPaymentReference());

        // fetch the charge
        $fetchedCharge = $this->heidelpay->fetchChargeById($charge->getPaymentId(), $charge->getId());

        // verify the fetched transaction matches the initial transaction
        $this->assertEquals($charge->expose(), $fetchedCharge->expose());
        $fetchedPayment = $fetchedCharge->getPayment();
        $this->assertEquals($payment->getPaymentType()->expose(), $fetchedPayment->getPaymentType()->expose());
        $this->assertEquals($payment->getCustomer()->expose(), $fetchedPayment->getCustomer()->expose());
        $this->assertEquals($payment->getMetadata()->expose(), $fetchedPayment->getMetadata()->expose());
        $this->assertEquals($payment->getBasket()->expose(), $fetchedPayment->getBasket()->expose());
    }

    /**
     * Verify charge accepts all parameters.
     *
     * @test
     */
    public function chargeWithCustomerShouldAcceptAllParameters(): void
    {
        // prepare test data
        /** @var InvoiceGuaranteed $ivg */
        $ivg = $this->heidelpay->createPaymentType(new InvoiceGuaranteed());
        $customer = $this->getMaximumCustomer();
        $customer->setShippingAddress($customer->getBillingAddress());
        $orderId = 'o'. self::generateRandomId();
        $metadata = (new Metadata())->addMetadata('key', 'value');
        $basket = $this->createBasket();
        $invoiceId = 'i'. self::generateRandomId();
        $paymentReference = 'paymentReference';

        // perform request
        $charge = $ivg->charge(119.0, 'EUR', self::RETURN_URL, $customer, $orderId, $metadata, $basket, null, $invoiceId, $paymentReference);

        // verify the data sent and received match
        $payment = $charge->getPayment();
        $this->assertSame($ivg, $payment->getPaymentType());
        $this->assertEquals(119.0, $charge->getAmount());
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
