<?php

/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocMissingThrowsInspection */
/**
 * This class defines unit tests to verify functionality of the Shipment transaction type.
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
 * @package  UnzerSDK\test\unit
 */

namespace UnzerSDK\test\unit\Resources\TransactionTypes;

use UnzerSDK\Resources\TransactionTypes\Shipment;
use UnzerSDK\test\BasePaymentTest;
use stdClass;

class ShipmentTest extends BasePaymentTest
{
    /**
     * Verify getters and setters.
     *
     * @test
     */
    public function gettersAndSettersShouldWorkProperly(): Shipment
    {
        $shipment = new Shipment();
        $this->assertNull($shipment->getAmount());
        $this->assertNull($shipment->getInvoiceId());

        $shipment->setAmount(123.4567);
        $shipment->setInvoiceId('NewInvoiceId');
        $this->assertEquals(123.4567, $shipment->getAmount());
        $this->assertEquals('NewInvoiceId', $shipment->getInvoiceId());

        return $shipment;
    }

    /**
     * Verify that an Shipment can be updated on handle response.
     *
     * @test
     *
     * @param Shipment $shipment
     *
     * @depends gettersAndSettersShouldWorkProperly
     */
    public function aShipmentShouldBeUpdatedThroughResponseHandling(Shipment $shipment): void
    {
        $testResponse = new stdClass();
        $testResponse->amount = '987.6543';
        $testResponse->invoiceId = 'AnotherInvoiceId';

        $shipment->handleResponse($testResponse);
        $this->assertEquals(987.6543, $shipment->getAmount());
        $this->assertEquals('AnotherInvoiceId', $shipment->getInvoiceId());
    }
}
