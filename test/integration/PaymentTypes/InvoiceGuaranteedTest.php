<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and functionality of the payment method Invoice Secured.
 *
 * Copyright (C) 2020 - today Unzer E-Com GmbH
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
 * @package  UnzerSDK\test\integration\PaymentTypes
 */
namespace UnzerSDK\test\integration\PaymentTypes;

use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\InvoiceSecured;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\test\BaseIntegrationTest;

/**
 * @deprecated since 1.2.0.0 PaylaterInvoice should be used instead in the future.
 */
class InvoiceGuaranteedTest extends BaseIntegrationTest
{
    /**
     * Verify, backwards compatibility regarding fetching payment type and map it to invoice secured class.
     *
     * @test
     */
    public function ivgTypeShouldBeFetchable(): InvoiceSecured
    {
        $ivgMock = $this->getMockBuilder(InvoiceSecured::class)->setMethods(['getUri'])->getMock();
        $ivgMock->method('getUri')->willReturn('/types/invoice-guaranteed');

        /** @var InvoiceSecured $ivgType */
        $ivgType = $this->unzer->createPaymentType($ivgMock);
        $this->assertInstanceOf(InvoiceSecured::class, $ivgType);
        $this->assertRegExp('/^s-ivg-[.]*/', $ivgType->getId());

        $fetchedType = $this->unzer->fetchPaymentType($ivgType->getId());
        $this->assertInstanceOf(InvoiceSecured::class, $fetchedType);
        $this->assertRegExp('/^s-ivg-[.]*/', $fetchedType->getId());

        return $fetchedType;
    }

    /**
     * Verify fetched ivg type can be charged
     *
     * @test
     *
     * @depends ivgTypeShouldBeFetchable
     *
     * @param InvoiceSecured $ivgType fetched ivg type.
     *
     * @throws UnzerApiException
     */
    public function ivgTypeShouldBeChargable(InvoiceSecured $ivgType)
    {
        $customer = $this->getMaximumCustomer();
        $charge = $ivgType->charge(100.00, 'EUR', 'https://unzer.com', $customer);

        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertTrue($charge->isPending());

        return $charge;
    }

    /**
     * Verify fetched ivg type can be shipped.
     *
     * @test
     *
     * @depends ivgTypeShouldBeChargable
     */
    public function ivgTypeShouldBeShippable(Charge $ivgCharge)
    {
        $invoiceId = 'i' . self::generateRandomId();

        $ship = $this->unzer->ship($ivgCharge->getPayment(), $invoiceId);
        // expect Payment to be pending after shipment.
        $this->assertTrue($ship->getPayment()->isPending());
        $this->assertNotNull($ship);
    }
}
