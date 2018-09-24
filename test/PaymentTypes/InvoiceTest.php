<?php
/**
 * These are integration tests to verify interface and functionality of the payment method invoice.
 *
 * @license Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.de>
 *
 * @package  heidelpay/test/integration
 */
namespace heidelpay\NmgPhpSdk\test\PaymentTypes;

use heidelpay\NmgPhpSdk\Constants\Currency;
use heidelpay\NmgPhpSdk\Resources\PaymentTypes\Invoice;
use heidelpay\NmgPhpSdk\test\BasePaymentTest;

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
