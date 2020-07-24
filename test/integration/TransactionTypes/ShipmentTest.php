<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines integration tests to verify interface and functionality of shipment.
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

use heidelpayPHP\Resources\PaymentTypes\InvoiceGuaranteed;
use heidelpayPHP\test\BaseIntegrationTest;

class ShipmentTest extends BaseIntegrationTest
{
    /**
     * Verify shipment transaction can be called.
     *
     * @test
     */
    public function shipmentShouldBeCreatableAndFetchable(): void
    {
        $ivg      = new InvoiceGuaranteed();
        $customer = $this->getMaximumCustomerInclShippingAddress()->setShippingAddress($this->getBillingAddress());

        $charge   = $this->heidelpay->charge(100.0, 'EUR', $ivg, self::RETURN_URL, $customer);
        $this->assertNotNull($charge->getId());
        $this->assertNotNull($charge);

        $shipment = $this->heidelpay->ship($charge->getPayment(), 'i'. self::generateRandomId(), 'i'. self::generateRandomId());
        $this->assertNotNull($shipment->getId());
        $this->assertNotNull($shipment);

        $fetchedShipment = $this->heidelpay->fetchShipment($shipment->getPayment()->getId(), $shipment->getId());
        $this->assertNotEmpty($fetchedShipment);
        $this->assertEquals($shipment->expose(), $fetchedShipment->expose());
    }

    /**
     * Verify shipment transaction can be called on the payment object.
     *
     * @test
     */
    public function shipmentCanBeCalledOnThePaymentObject(): void
    {
        $invoiceGuaranteed = new InvoiceGuaranteed();
        $customer          = $this->getMaximumCustomerInclShippingAddress()->setShippingAddress($this->getBillingAddress());
        $charge            = $this->heidelpay->charge(100.0, 'EUR', $invoiceGuaranteed, self::RETURN_URL, $customer);

        $payment  = $charge->getPayment();
        $shipment = $payment->ship('i'. self::generateRandomId(), 'o'. self::generateRandomId());
        $this->assertNotNull($shipment);
        $this->assertNotEmpty($shipment->getId());
        $this->assertNotEmpty($shipment->getUniqueId());
        $this->assertNotEmpty($shipment->getShortId());

        $traceId = $shipment->getTraceId();
        $this->assertNotEmpty($traceId);
        $this->assertSame($traceId, $shipment->getPayment()->getTraceId());

        $fetchedShipment = $this->heidelpay->fetchShipment($shipment->getPayment()->getId(), $shipment->getId());
        $this->assertNotEmpty($fetchedShipment);
        $this->assertEquals($shipment->expose(), $fetchedShipment->expose());
    }

    /**
     * Verify shipment can be performed with payment object.
     *
     * @test
     */
    public function shipmentShouldBePossibleWithPaymentObject(): void
    {
        $invoiceGuaranteed = new InvoiceGuaranteed();
        $customer          = $this->getMaximumCustomerInclShippingAddress()->setShippingAddress($this->getBillingAddress());
        $charge            = $this->heidelpay->charge(100.0, 'EUR', $invoiceGuaranteed, self::RETURN_URL, $customer);

        $payment  = $charge->getPayment();
        $shipment = $this->heidelpay->ship($payment, 'i'. self::generateRandomId(), 'o'. self::generateRandomId());
        $this->assertNotNull($shipment->getId());
        $this->assertNotNull($shipment);
    }

    /**
     * Verify transaction status.
     *
     * @test
     */
    public function shipmentStatusIsSetCorrectly(): void
    {
        $invoiceGuaranteed = new InvoiceGuaranteed();
        $customer          = $this->getMaximumCustomerInclShippingAddress()->setShippingAddress($this->getBillingAddress());
        $charge            = $this->heidelpay->charge(100.0, 'EUR', $invoiceGuaranteed, self::RETURN_URL, $customer);

        $payment  = $charge->getPayment();
        $shipment = $this->heidelpay->ship($payment, 'i'. self::generateRandomId(), 'o'. self::generateRandomId());
        $this->assertSuccess($shipment);
    }
}
