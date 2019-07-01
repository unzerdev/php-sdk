<?php
/**
 * This class defines integration tests to verify interface and functionality of the payment method invoice guaranteed.
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
use heidelpayPHP\Resources\PaymentTypes\InvoiceGuaranteed;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class InvoiceGuaranteedTest extends BasePaymentTest
{
    /**
     * Verifies invoice guaranteed payment type can be created.
     *
     * @test
     *
     * @return InvoiceGuaranteed
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function invoiceGuaranteedTypeShouldBeCreatable(): InvoiceGuaranteed
    {
        /** @var InvoiceGuaranteed $invoiceGuaranteed */
        $invoiceGuaranteed = $this->heidelpay->createPaymentType(new InvoiceGuaranteed());
        $this->assertInstanceOf(InvoiceGuaranteed::class, $invoiceGuaranteed);
        $this->assertNotNull($invoiceGuaranteed->getId());

        return $invoiceGuaranteed;
    }

    /**
     * Verify invoice guaranteed can be shipped.
     *
     * @test
     *
     * @param InvoiceGuaranteed $invoiceGuaranteed
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     * @depends invoiceGuaranteedTypeShouldBeCreatable
     */
    public function verifyInvoiceGuaranteedShipment(InvoiceGuaranteed $invoiceGuaranteed)
    {
        $charge = $invoiceGuaranteed->charge(
            100.0,
            'EUR',
            self::RETURN_URL,
            $this->getMaximumCustomerInclShippingAddress()->setShippingAddress($this->getBillingAddress())
        );
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertNotEmpty($charge->getIban());
        $this->assertNotEmpty($charge->getBic());
        $this->assertNotEmpty($charge->getHolder());
        $this->assertNotEmpty($charge->getDescriptor());


        $shipment = $this->heidelpay->ship($charge->getPayment());
        $this->assertNotNull($shipment);
        $this->assertNotEmpty($shipment->getId());
    }

    /**
     * Verify invoice guaranteed can be charged and cancelled.
     *
     * @test
     *
     * @param InvoiceGuaranteed $invoiceGuaranteed
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     * @depends invoiceGuaranteedTypeShouldBeCreatable
     */
    public function verifyInvoiceGuaranteedCanBeChargedAndCancelled(InvoiceGuaranteed $invoiceGuaranteed)
    {
        $charge = $invoiceGuaranteed->charge(
            100.0,
            'EUR',
            self::RETURN_URL,
            $this->getMaximumCustomerInclShippingAddress()->setShippingAddress($this->getBillingAddress())
        );
        $this->assertTrue($charge->isPending());
        $this->assertFalse($charge->isError());
        $this->assertFalse($charge->isSuccess());

        $cancel = $charge->cancel();
        $this->assertNotNull($cancel);
        $this->assertNotEmpty($cancel->getId());
    }

    /**
     * Verify that an invoice guaranteed object can be fetched from the api.
     *
     * @test
     *
     * @param InvoiceGuaranteed $invoiceGuaranteed
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     * @depends invoiceGuaranteedTypeShouldBeCreatable
     */
    public function invoiceGuaranteedTypeCanBeFetched(InvoiceGuaranteed $invoiceGuaranteed)
    {
        $fetchedInvoiceGuaranteed = $this->heidelpay->fetchPaymentType($invoiceGuaranteed->getId());
        $this->assertInstanceOf(InvoiceGuaranteed::class, $fetchedInvoiceGuaranteed);
        $this->assertEquals($invoiceGuaranteed->getId(), $fetchedInvoiceGuaranteed->getId());
    }

    /**
     * Verify ivg will throw error if addresses do not match.
     *
     * @test
     *
     * @param InvoiceGuaranteed $invoiceGuaranteed
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     * @depends invoiceGuaranteedTypeShouldBeCreatable
     */
    public function ivgShouldThrowErrorIfAddressesDoNotMatch(InvoiceGuaranteed $invoiceGuaranteed)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_ADDRESSES_DO_NOT_MATCH);

        $invoiceGuaranteed->charge(
            100.0,
            'EUR',
            self::RETURN_URL,
            $this->getMaximumCustomerInclShippingAddress()
        );
    }
}
