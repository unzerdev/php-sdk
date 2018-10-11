<?php
/**
 * This class defines integration tests to verify interface and functionality of shipment.
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
 * @copyright Copyright © 2016-present heidelpay GmbH. All rights reserved.
 *
 * @link  http://dev.heidelpay.com/
 *
 * @author  Simon Gabriel <development@heidelpay.com>
 *
 * @package  heidelpay/mgw_sdk/tests/integration
 */
namespace heidelpay\MgwPhpSdk\test\integration;

use heidelpay\MgwPhpSdk\Constants\Currency;
use heidelpay\MgwPhpSdk\Resources\PaymentTypes\InvoiceGuaranteed;
use heidelpay\MgwPhpSdk\test\BasePaymentTest;

class ShipmentTest extends BasePaymentTest
{
    /**
     * Verify shipment transaction can be called.
     *
     * @test
     */
    public function shipmentCanBeCalledForInvoiceGuaranteed()
    {
        $invoiceGuaranteed = new InvoiceGuaranteed();
        $authorize = $this->heidelpay->authorize(100.0, Currency::EURO, $invoiceGuaranteed, self::RETURN_URL, $this->getMaximumCustomer());
        $this->assertNotNull($authorize->getId());
        $this->assertNotNull($authorize);

        $shipment = $this->heidelpay->ship($authorize->getPayment());
        $this->assertNotNull($shipment->getId());
        $this->assertNotNull($shipment);

        $fetchedShipment = $this->heidelpay->fetchShipmentByPayment($shipment->getParentResource(), $shipment->getId());
        $this->assertNotEmpty($fetchedShipment);
        $this->assertEquals($shipment->expose(), $fetchedShipment->expose());

        $secondFetchedShipment = $this->heidelpay->fetchShipment($shipment->getPayment()->getId(), $shipment->getId());
        $this->assertNotEmpty($secondFetchedShipment);
        $this->assertEquals($shipment->expose(), $secondFetchedShipment->expose());
    }
}
