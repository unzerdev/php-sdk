<?php
/**
 * This class defiens integration tests to verify interface and functionality
 * of the payment method invoice.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/tests/integration/payment_types
 */
namespace heidelpay\MgwPhpSdk\test\integration\PaymentTypes;

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
