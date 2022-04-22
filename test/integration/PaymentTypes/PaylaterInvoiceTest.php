<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and functionality of the payment method Paylater Invoice.
 *
 * Copyright (C) 2022 - today Unzer E-Com GmbH
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
 * @link  https://docs.unzer.com/
 *
 * @author  David Owusu <development@unzer.com>
 *
 * @package  UnzerSDK\test\integration\PaymentTypes
 */
namespace UnzerSDK\test\integration\PaymentTypes;

use UnzerSDK\Resources\PaymentTypes\PaylaterInvoice;
use UnzerSDK\test\BaseIntegrationTest;

class PaylaterInvoiceTest extends BaseIntegrationTest
{
    /**
     * Verifies Invoice Secured payment type can be created.
     *
     * @test
     *
     * @return PaylaterInvoice
     */
    public function paylaterInvoiceTestTypeShouldBeCreatableAndFetchable(): PaylaterInvoice
    {
        /** @var PaylaterInvoice $invoice */
        $invoice = $this->unzer->createPaymentType(new PaylaterInvoice());
        $this->assertInstanceOf(PaylaterInvoice::class, $invoice);
        $this->assertNotNull($invoice->getId());

        $fetchedInvoice = $this->unzer->fetchPaymentType($invoice->getId());
        $this->assertInstanceOf(PaylaterInvoice::class, $fetchedInvoice);
        $this->assertEquals($invoice->getId(), $fetchedInvoice->getId());
        $this->assertRegExp('/^s-piv-[.]*/', $fetchedInvoice->getId());

        return $invoice;
    }
}
