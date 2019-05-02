<?php
/**
 * This class defines unit tests to verify functionality of the Shipment transaction type.
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
 * @package  heidelpayPHP/test/unit
 */
namespace heidelpayPHP\test\unit\Resources\TransactionTypes;

use heidelpayPHP\Exceptions\HeidelpayApiException;
use heidelpayPHP\Resources\TransactionTypes\Shipment;
use heidelpayPHP\test\BaseUnitTest;
use PHPUnit\Framework\Exception;
use RuntimeException;
use stdClass;

class ShipmentTest extends BaseUnitTest
{
    /**
     * Verify getters and setters.
     *
     * @test
     *
     * @throws Exception
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
     * @throws Exception
     * @throws HeidelpayApiException
     * @throws RuntimeException
     * @depends gettersAndSettersShouldWorkProperly
     */
    public function aShipmentShouldBeUpdatedThroughResponseHandling(Shipment $shipment)
    {
        $testResponse = new stdClass();
        $testResponse->amount = '987.6543';
        $testResponse->invoiceId = 'AnotherInvoiceId';

        $shipment->handleResponse($testResponse);
        $this->assertEquals(987.6543, $shipment->getAmount());
        $this->assertEquals('AnotherInvoiceId', $shipment->getInvoiceId());
    }
}
