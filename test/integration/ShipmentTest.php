<?php
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
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpayPHP/test/integration
 */
namespace heidelpayPHP\test\integration;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\PaymentTypes\InvoiceGuaranteed;
use heidelpayPHP\test\BasePaymentTest;
use RuntimeException;

class ShipmentTest extends BasePaymentTest
{
    /**
     * Verify shipment transaction can be called.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function shipmentShouldBeCreatableAndFetchable()
    {
        $invoiceGuaranteed = new InvoiceGuaranteed();
        $charge = $this->heidelpay->charge(
            100.0,
            'EUR',
            $invoiceGuaranteed,
            self::RETURN_URL,
            $this->getMaximumCustomerInclShippingAddress()->setShippingAddress($this->getBillingAddress())
        );
        $this->assertNotNull($charge->getId());
        $this->assertNotNull($charge);

        $shipment = $this->heidelpay->ship($charge->getPayment());
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
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function shipmentCanBeCalledOnThePaymentObject()
    {
        $invoiceGuaranteed = new InvoiceGuaranteed();
        $charge = $this->heidelpay->charge(
            100.0,
            'EUR',
            $invoiceGuaranteed,
            self::RETURN_URL,
            $this->getMaximumCustomerInclShippingAddress()->setShippingAddress($this->getBillingAddress())
        );

        $payment  = $charge->getPayment();
        $shipment = $payment->ship();
        $this->assertNotNull($shipment);
        $this->assertNotEmpty($shipment->getId());
        $this->assertNotEmpty($shipment->getUniqueId());
        $this->assertNotEmpty($shipment->getShortId());

        $fetchedShipment = $this->heidelpay->fetchShipment($shipment->getPayment()->getId(), $shipment->getId());
        $this->assertNotEmpty($fetchedShipment);
        $this->assertEquals($shipment->expose(), $fetchedShipment->expose());
    }

    /**
     * Verify shipment can be performed with payment object.
     *
     * @test
     *
     * @throws RuntimeException
     * @throws HeidelpayApiException
     */
    public function shipmentShouldBePossibleWithPaymentObject()
    {
        $invoiceGuaranteed = new InvoiceGuaranteed();
        $charge = $this->heidelpay->charge(
            100.0,
            'EUR',
            $invoiceGuaranteed,
            self::RETURN_URL,
            $this->getMaximumCustomerInclShippingAddress()->setShippingAddress($this->getBillingAddress())
        );

        $payment  = $charge->getPayment();
        $shipment = $this->heidelpay->ship($payment);
        $this->assertNotNull($shipment->getId());
        $this->assertNotNull($shipment);
    }

    /**
     * Verify transaction status.
     *
     * @test
     *
     * @throws HeidelpayApiException
     * @throws RuntimeException
     */
    public function shipmentStatusIsSetCorrectly()
    {
        $invoiceGuaranteed = new InvoiceGuaranteed();
        $charge = $this->heidelpay->charge(
            100.0,
            'EUR',
            $invoiceGuaranteed,
            self::RETURN_URL,
            $this->getMaximumCustomerInclShippingAddress()->setShippingAddress($this->getBillingAddress())
        );

        $payment  = $charge->getPayment();
        $shipment = $this->heidelpay->ship($payment);
        $this->assertTrue($shipment->isSuccess());
        $this->assertFalse($shipment->isPending());
        $this->assertFalse($shipment->isError());
    }
}
