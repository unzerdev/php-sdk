<?php
/**
 * This class defines integration tests to verify interface and functionality of the payment method invoice.
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
 * @package  heidelpayPHP/test/integration/payment_types
 */
namespace heidelpayPHP\test\integration\PaymentTypes;

use heidelpayPHP\Constants\ApiResponseCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\PaymentTypes\Invoice;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class InvoiceTest extends BasePaymentTest
{
    /**
     * Verifies invoice payment type can be created.
     *
     * @test
     *
     * @return Invoice
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function invoiceTypeShouldBeCreatable(): Invoice
    {
        /** @var Invoice $invoice */
        $invoice = $this->heidelpay->createPaymentType(new Invoice());
        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertNotNull($invoice->getId());

        return $invoice;
    }

    /**
     * Verify invoice is not authorizable.
     *
     * @test
     *
     * @param Invoice $invoice
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @depends invoiceTypeShouldBeCreatable
     */
    public function verifyInvoiceIsNotAuthorizable(Invoice $invoice)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->heidelpay->authorize(1.0, 'EUR', $invoice, self::RETURN_URL);
    }

    /**
     * Verify invoice is not shippable.
     *
     * @test
     *
     * @param Invoice $invoice
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @depends invoiceTypeShouldBeCreatable
     */
    public function verifyInvoiceIsNotShippable(Invoice $invoice)
    {
        $charge = $invoice->charge(1.0, 'EUR', self::RETURN_URL);
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertNotEmpty($charge->getIban());
        $this->assertNotEmpty($charge->getBic());
        $this->assertNotEmpty($charge->getHolder());
        $this->assertNotEmpty($charge->getDescriptor());

        $payment = $charge->getPayment();

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_SHIP_NOT_ALLOWED);

        $this->heidelpay->ship($payment);
    }

    /**
     * Verify invoice charge can be canceled.
     *
     * @test
     *
     * @param Invoice $invoice
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @depends invoiceTypeShouldBeCreatable
     */
    public function verifyInvoiceChargeCanBeCanceled(Invoice $invoice)
    {
        $charge = $invoice->charge(1.0, 'EUR', self::RETURN_URL);
        $cancellation = $charge->cancel();
        $this->assertNotNull($cancellation);
        $this->assertNotNull($cancellation->getId());#
    }

    /**
     * Verify that an invoice object can be fetched from the api.
     *
     * @test
     *
     * @param Invoice $invoice
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @depends invoiceTypeShouldBeCreatable
     */
    public function invoiceTypeCanBeFetched(Invoice $invoice)
    {
        $fetchedInvoice = $this->heidelpay->fetchPaymentType($invoice->getId());
        $this->assertInstanceOf(Invoice::class, $fetchedInvoice);
        $this->assertEquals($invoice->getId(), $fetchedInvoice->getId());
    }
}
