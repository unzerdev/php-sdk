<?php
/**
 * This class defines integration tests to verify interface and functionality of the payment method Invoice Factoring.
 *
 * Copyright (C) 2019 heidelpay GmbH
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
use heidelpayPHP\Constants\CancelReasonCodes;
use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\PaymentTypes\InvoiceFactoring;
use heidelpayPHP\Resources\TransactionTypes\Charge;
use heidelpayPHP\test\BasePaymentTest;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use RuntimeException;

class InvoiceFactoringTest extends BasePaymentTest
{
    /**
     * Verifies Invoice Factoring payment type can be created.
     *
     * @test
     *
     * @return InvoiceFactoring
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function invoiceFactoringTypeShouldBeCreatableAndFetchable(): InvoiceFactoring
    {
        /** @var InvoiceFactoring $invoice */
        $invoice = $this->heidelpay->createPaymentType(new InvoiceFactoring());
        $this->assertInstanceOf(InvoiceFactoring::class, $invoice);
        $this->assertNotNull($invoice->getId());

        $fetchedInvoice = $this->heidelpay->fetchPaymentType($invoice->getId());
        $this->assertInstanceOf(InvoiceFactoring::class, $fetchedInvoice);
        $this->assertEquals($invoice->getId(), $fetchedInvoice->getId());

        return $invoice;
    }

    /**
     * Verify Invoice Factoring is not authorizable.
     *
     * @test
     *
     * @param InvoiceFactoring $invoice
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @depends invoiceFactoringTypeShouldBeCreatableAndFetchable
     */
    public function verifyInvoiceIsNotAuthorizable(InvoiceFactoring $invoice)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_TRANSACTION_AUTHORIZE_NOT_ALLOWED);

        $this->heidelpay->authorize(1.0, 'EUR', $invoice, self::RETURN_URL);
    }

    /**
     * Verify Invoice Factoring needs a customer object
     *
     * @test
     * @depends invoiceFactoringTypeShouldBeCreatableAndFetchable
     *
     * @param InvoiceFactoring $invoiceFactoring
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function invoiceFactoringShouldRequiresCustomer(InvoiceFactoring $invoiceFactoring)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_IVF_REQUIRES_CUSTOMER);
        $this->heidelpay->charge(1.0, 'EUR', $invoiceFactoring, self::RETURN_URL);
    }

    /**
     * Verify Invoice Factoring is chargeable.
     *
     * @test
     * @depends invoiceFactoringTypeShouldBeCreatableAndFetchable
     *
     * @param InvoiceFactoring $invoiceFactoring
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function invoiceFactoringRequiresBasket(InvoiceFactoring $invoiceFactoring)
    {
        $customer = $this->getMaximumCustomer();
        $customer->setShippingAddress($customer->getBillingAddress());

        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_IVF_REQUIRES_BASKET);

        $invoiceFactoring->charge(1.0, 'EUR', self::RETURN_URL, $customer);
    }

    /**
     * Verify Invoice Factoring is chargeable.
     *
     * @test
     * @depends invoiceFactoringTypeShouldBeCreatableAndFetchable
     *
     * @param InvoiceFactoring $invoiceFactoring
     *
     * @return Charge
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws AssertionFailedError
     */
    public function invoiceFactoringShouldBeChargeable(InvoiceFactoring $invoiceFactoring): Charge
    {
        $customer = $this->getMaximumCustomer();
        $customer->setShippingAddress($customer->getBillingAddress());

        $basket = $this->createBasket();
        $charge = $invoiceFactoring->charge(
            100.0,
            'EUR',
            self::RETURN_URL,
            $customer,
            $basket->getOrderId(),
            null,
            $basket
        );
        $this->assertNotNull($charge);
        $this->assertNotEmpty($charge->getId());
        $this->assertNotEmpty($charge->getIban());
        $this->assertNotEmpty($charge->getBic());
        $this->assertNotEmpty($charge->getHolder());
        $this->assertNotEmpty($charge->getDescriptor());

        return $charge;
    }

    /**
     * Verify Invoice Factoring is not shippable on heidelpay object.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws Exception
     */
    public function verifyInvoiceFactoringIsNotShippableWoInvoiceIdOnHeidelpayObject()
    {
        // create payment type
        /** @var InvoiceFactoring $invoiceFactoring */
        $invoiceFactoring = $this->heidelpay->createPaymentType(new InvoiceFactoring());

        // perform charge
        $customer = $this->getMaximumCustomer();
        $customer->setShippingAddress($customer->getBillingAddress());

        $basket = $this->createBasket();
        $charge = $invoiceFactoring->charge(
            100.0,
            'EUR',
            self::RETURN_URL,
            $customer,
            $basket->getOrderId(),
            null,
            $basket
        );

        // perform shipment
        $payment = $charge->getPayment();
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_SHIPPING_REQUIRES_INVOICE_ID);
        $this->heidelpay->ship($payment);
    }

    /**
     * Verify Invoice Factoring is not shippable on payment object.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @throws Exception
     */
    public function verifyInvoiceFactoringIsNotShippableWoInvoiceIdOnPaymentObject()
    {
        // create payment type
        /** @var InvoiceFactoring $invoiceFactoring */
        $invoiceFactoring = $this->heidelpay->createPaymentType(new InvoiceFactoring());

        // perform charge
        $customer = $this->getMaximumCustomer();
        $customer->setShippingAddress($customer->getBillingAddress());

        $basket = $this->createBasket();
        $charge = $invoiceFactoring->charge(
            100.0,
            'EUR',
            self::RETURN_URL,
            $customer,
            $basket->getOrderId(),
            null,
            $basket
        );

        // perform shipment
        $payment = $charge->getPayment();
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_SHIPPING_REQUIRES_INVOICE_ID);
        $payment->ship();
    }

    /**
     * Verify Invoice Factoring shipment with invoice id on heidelpay object.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function verifyInvoiceFactoringShipmentWithInvoiceIdOnHeidelpayObject()
    {
        // create payment type
        /** @var InvoiceFactoring $invoiceFactoring */
        $invoiceFactoring = $this->heidelpay->createPaymentType(new InvoiceFactoring());

        // perform charge
        $customer = $this->getMaximumCustomer();
        $customer->setShippingAddress($customer->getBillingAddress());

        $basket = $this->createBasket();
        $charge = $invoiceFactoring->charge(
            100.0,
            'EUR',
            self::RETURN_URL,
            $customer,
            $basket->getOrderId(),
            null,
            $basket
        );

        // perform shipment
        $payment   = $charge->getPayment();
        $invoiceId = substr(str_replace(['0.',' '], '', microtime(false)), 0, 16);
        $shipment  = $this->heidelpay->ship($payment, $invoiceId);
        $this->assertNotNull($shipment->getId());
        $this->assertEquals($invoiceId, $shipment->getInvoiceId());
    }

    /**
     * Verify Invoice Factoring shipment with invoice id on payment object.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function verifyInvoiceFactoringShipmentWithInvoiceIdOnPaymentObject()
    {
        // create payment type
        /** @var InvoiceFactoring $invoiceFactoring */
        $invoiceFactoring = $this->heidelpay->createPaymentType(new InvoiceFactoring());

        // perform charge
        $customer = $this->getMaximumCustomer();
        $customer->setShippingAddress($customer->getBillingAddress());

        $basket = $this->createBasket();
        $charge = $invoiceFactoring->charge(
            100.0,
            'EUR',
            self::RETURN_URL,
            $customer,
            $basket->getOrderId(),
            null,
            $basket
        );

        $payment   = $charge->getPayment();
        $invoiceId = substr(str_replace(['0.',' '], '', microtime(false)), 0, 16);
        $shipment  = $payment->ship($invoiceId);
        $this->assertNotNull($shipment->getId());
        $this->assertEquals($invoiceId, $shipment->getInvoiceId());
    }

    /**
     * Verify Invoice Factoring shipment with pre set invoice id
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function verifyInvoiceFactoringShipmentWithPreSetInvoiceId()
    {
        /** @var InvoiceFactoring $invoiceFactoring */
        $invoiceFactoring = $this->heidelpay->createPaymentType(new InvoiceFactoring());

        $customer = $this->getMaximumCustomer();
        $customer->setShippingAddress($customer->getBillingAddress());

        $basket = $this->createBasket();
        $invoiceId = substr(str_replace(['0.',' '], '', microtime(false)), 0, 16);
        $charge = $invoiceFactoring->charge(
            100.0,
            'EUR',
            self::RETURN_URL,
            $customer,
            $basket->getOrderId(),
            null,
            $basket,
            null,
            $invoiceId
        );

        $payment   = $charge->getPayment();
        $shipment  = $this->heidelpay->ship($payment);
        $this->assertNotNull($shipment->getId());
        $this->assertEquals($invoiceId, $shipment->getInvoiceId());
    }

    /**
     * Verify Invoice Factoring charge can canceled.
     *
     * @test
     *
     * @param Charge $charge
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @depends invoiceFactoringShouldBeChargeable
     */
    public function verifyInvoiceChargeCanBeCanceled(Charge $charge)
    {
        $cancellation = $charge->cancel($charge->getAmount(), CancelReasonCodes::REASON_CODE_CANCEL);
        $this->assertNotNull($cancellation);
        $this->assertNotNull($cancellation->getId());
    }

    /**
     * Verify Invoice Factoring charge cancel throws exception if the amount is missing.
     *
     * @test
     *
     * @param Charge $charge
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @depends invoiceFactoringShouldBeChargeable
     */
    public function verifyInvoiceChargeCanNotBeCancelledWoAmount(Charge $charge)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_AMOUNT_IS_MISSING);
        $charge->cancel();
    }

    /**
     * Verify Invoice Factoring charge cancel throws exception if the reason is missing.
     *
     * @test
     *
     * @param Charge $charge
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @depends invoiceFactoringShouldBeChargeable
     */
    public function verifyInvoiceChargeCanNotBeCancelledWoReasonCode(Charge $charge)
    {
        $this->expectException(HeidelpayApiException::class);
        $this->expectExceptionCode(ApiResponseCodes::API_ERROR_CANCEL_REASON_CODE_IS_MISSING);
        $charge->cancel(100.0);
    }
}
