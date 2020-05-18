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
 * @link  https://docs.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP\test\integration\PaymentTypes
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
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function invoiceGuaranteedTypeShouldBeCreatable()
    {
        /** @var InvoiceGuaranteed $invoiceGuaranteed */
        $invoiceGuaranteed = $this->heidelpay->createPaymentType(new InvoiceGuaranteed());
        $this->assertInstanceOf(InvoiceGuaranteed::class, $invoiceGuaranteed);
        $this->assertNotNull($invoiceGuaranteed->getId());
    }

    /**
     * Verify invoice guaranteed can be shipped.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function verifyInvoiceGuaranteedShipment()
    {
        /** @var InvoiceGuaranteed $invoiceGuaranteed */
        $invoiceGuaranteed = $this->heidelpay->createPaymentType(new InvoiceGuaranteed());
        $customer = $this->getMaximumCustomerInclShippingAddress()->setShippingAddress($this->getBillingAddress());
        $charge   = $invoiceGuaranteed->charge(100.0, 'EUR', self::RETURN_URL, $customer);
        $this->assertTransactionResourceHasBeenCreated($charge);

        $this->assertNotEmpty($charge->getIban());
        $this->assertNotEmpty($charge->getBic());
        $this->assertNotEmpty($charge->getHolder());
        $this->assertNotEmpty($charge->getDescriptor());

        $shipment = $this->heidelpay->ship($charge->getPayment(), 'i' . self::generateRandomId(), 'o' . self::generateRandomId());
        $this->assertTransactionResourceHasBeenCreated($shipment);
    }

    /**
     * Verify invoice guaranteed can be charged and cancelled.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function verifyInvoiceGuaranteedCanBeChargedAndCancelled()
    {
        /** @var InvoiceGuaranteed $invoiceGuaranteed */
        $invoiceGuaranteed = $this->heidelpay->createPaymentType(new InvoiceGuaranteed());
        $customer = $this->getMaximumCustomerInclShippingAddress()->setShippingAddress($this->getBillingAddress());
        $charge   = $invoiceGuaranteed->charge(100.0, 'EUR', self::RETURN_URL, $customer);
        $this->assertPending($charge);

        $cancel = $charge->cancel();
        $this->assertTransactionResourceHasBeenCreated($cancel);
    }

    /**
     * Verify that an invoice guaranteed object can be fetched from the api.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function invoiceGuaranteedTypeCanBeFetched()
    {
        /** @var InvoiceGuaranteed $invoiceGuaranteed */
        $invoiceGuaranteed = $this->heidelpay->createPaymentType(new InvoiceGuaranteed());
        $fetchedInvoiceGuaranteed = $this->heidelpay->fetchPaymentType($invoiceGuaranteed->getId());
        $this->assertInstanceOf(InvoiceGuaranteed::class, $fetchedInvoiceGuaranteed);
        $this->assertEquals($invoiceGuaranteed->getId(), $fetchedInvoiceGuaranteed->getId());
    }

    /**
     * Verify ivg will throw error if addresses do not match.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function ivgShouldThrowErrorIfAddressesDoNotMatch()
    {
        /** @var InvoiceGuaranteed $invoiceGuaranteed */
        $invoiceGuaranteed = $this->heidelpay->createPaymentType(new InvoiceGuaranteed());
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_ADDRESSES_DO_NOT_MATCH);

        $invoiceGuaranteed->charge(100.0, 'EUR', self::RETURN_URL, $this->getMaximumCustomerInclShippingAddress());
    }

    /**
     * Verify invoice guaranteed invoiceId can be set during charge and shipment.
     * Verify the invoiceId set during shipping overrides the previously set invoiceId.
     *
     * @test
     *
     * @throws HeidelpayApiException A HeidelpayApiException is thrown if there is an error returned on API-request.
     * @throws RuntimeException      A RuntimeException is thrown when there is an error while using the SDK.
     */
    public function verifyInvoiceIdInShipmentWillOverrideTheOneFromCharge()
    {
        /** @var InvoiceGuaranteed $invoiceGuaranteed */
        $invoiceGuaranteed = $this->heidelpay->createPaymentType(new InvoiceGuaranteed());
        $customer          = $this->getMaximumCustomerInclShippingAddress()->setShippingAddress($this->getBillingAddress());

        $invoiceId  = 'i' . self::generateRandomId();
        $charge     = $invoiceGuaranteed->charge(100.0, 'EUR', self::RETURN_URL, $customer, null, null, null, null, $invoiceId);
        $chargeInvoiceId = $charge->getPayment()->getInvoiceId();

        $newInvoiceId = $invoiceId . 'X';
        $shipment = $this->heidelpay->ship($charge->getPayment(), $newInvoiceId);
        $shipmentInvoiceId = $shipment->getPayment()->getInvoiceId();

        $this->assertNotEquals($chargeInvoiceId, $shipmentInvoiceId);
    }
}
