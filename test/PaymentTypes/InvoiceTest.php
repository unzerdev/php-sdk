<?php
/**
 * This class defiens integration tests to verify interface and functionality
 * of the payment method invoice.
 *
 * LICENSE
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/tests/integration/payment_types
 */
namespace heidelpay\MgwPhpSdk\test\PaymentTypes;

use heidelpay\MgwPhpSdk\Constants\Currency;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\Invoice;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;

class InvoiceTest extends BasePaymentTest
{
    /**
     * Verifies invoice payment type can be created.
     *
     * @test
     * @return Invoice
     */
    public function invoiceTypeShouldBeCreatable(): Invoice
    {
        /** @var Invoice $invoice */
        $invoice = new Invoice();
        $invoice = $this->heidelpay->createPaymentType($invoice);
        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertNotNull($invoice->getId());

        return $invoice;
    }

    /**
     * Verify invoice is chargeable.
     *
     * @test
     * @param Invoice $invoice
     * @depends invoiceTypeShouldBeCreatable
     *
     * todo: charge != shipment
     * todo: shipment works only with prior authorization
     */
    public function verifyInvoiceShipment(Invoice $invoice)
    {
		$charge = $invoice->charge(1.0, Currency::EUROPEAN_EURO, self::RETURN_URL);
		$this->assertNotNull($charge);
		$this->assertNotNull($charge->getId());
        $this->assertNotNull($charge->getRedirectUrl());
    }

    /**
     * Verify that an invoice object can be fetched from the api.
     *
     * @test
     * @param Invoice $invoice
     * @depends invoiceTypeShouldBeCreatable
     */
    public function invoiceTypeCanBeFetched(Invoice $invoice)
    {
		$fetchedInvoice = $this->heidelpay->fetchPaymentType($invoice->getId());
		$this->assertInstanceOf(Invoice::class, $fetchedInvoice);
		$this->assertEquals($invoice->getId(), $fetchedInvoice->getId());
    }
}
